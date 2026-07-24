<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Customer_ViewAll|Customer_View', ['only' => ['index', 'show']]);
        $this->middleware('permission:Customer_Add', ['only' => ['create', 'store']]);
        $this->middleware('permission:Customer_Edit', ['only' => ['edit', 'update']]);
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);

        $query = Customer::query()
            ->whereNull('deleted_at')
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('customer_id', 'LIKE', "%{$search}%")
                    ->orWhere('username', 'LIKE', "%{$search}%")
                    ->orWhere('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('mobile_no', 'LIKE', "%{$search}%")
                    ->orWhere('nationality', 'LIKE', "%{$search}%")
                    ->orWhere('city', 'LIKE', "%{$search}%")
                    ->orWhere('country', 'LIKE', "%{$search}%");
            });
        }

        $customers = $query->paginate($perPage)->withQueryString();

        return view('admin.customers.index', compact('customers', 'search', 'perPage'));
    }

    public function create()
    {
        return view('admin.customers.create', [
            'customer' => null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateCustomer($request);

        $speedResponse = $this->createSpeedCustomer($validated);
        $speedResult = $this->extractSpeedResult($speedResponse);

        if (($speedResponse['success'] ?? true) === false || empty($speedResult)) {
            return back()
                ->withInput()
                ->with('error', $speedResponse['error'] ?? 'Failed to create customer in Speed system.');
        }

        $createdCustomer = $speedResult;
        $speedCustomerId = $this->resolveSpeedCustomerId($createdCustomer);

        if (empty($speedCustomerId)) {
            return back()
                ->withInput()
                ->with('error', 'Customer was created in Speed system but the customer id was not returned.');
        }

        $speedCustomerData = $this->getCustomerById($speedCustomerId);
        $localData = $this->mapLocalCustomerData(
            $validated,
            $this->extractSpeedResult($speedCustomerData),
            $speedCustomerId
        );
        $localData['password'] = Hash::make($validated['password']);
        $localData['created_by'] = Auth::id();
        $localData['updated_by'] = Auth::id();

        $customer = Customer::create($localData);

        return redirect()
            ->route('admin.customers.show', $customer->id)
            ->with('success', 'Customer created successfully.');
    }

    public function show(int $id)
    {
        $customer = Customer::withTrashed()->findOrFail($id);

        return view('admin.customers.show', compact('customer'));
    }

    public function edit(int $id)
    {
        $customer = Customer::withTrashed()->findOrFail($id);

        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, int $id)
    {
        $customer = Customer::withTrashed()->findOrFail($id);
        $validated = $this->validateCustomer($request, $customer);

        if (empty($customer->customer_id)) {
            return back()
                ->withInput()
                ->with('error', 'Speed customer ID not found for this customer.');
        }

        $speedResponse = $this->updateSpeedCustomer($request, $customer->customer_id);
        $speedResult = $this->extractSpeedResult($speedResponse);

        if (($speedResponse['success'] ?? true) === false || empty($speedResult)) {
            return back()
                ->withInput()
                ->with('error', $speedResponse['error'] ?? 'Failed to update customer in Speed system.');
        }

        $speedCustomerData = $this->getCustomerById($customer->customer_id);
        $localData = $this->mapLocalCustomerData(
            $validated,
            $this->extractSpeedResult($speedCustomerData),
            $customer->customer_id,
            $customer
        );

        if (!empty($validated['password'])) {
            $localData['password'] = Hash::make($validated['password']);
        }

        $localData['updated_by'] = Auth::id();

        $customer->update($localData);

        return redirect()
            ->route('admin.customers.show', $customer->id)
            ->with('success', 'Customer updated successfully.');
    }

    private function validateCustomer(Request $request, ?Customer $customer = null): array
    {
        $customerId = $customer?->id;

        return Validator::make($request->all(), [
            'username' => [
                'required',
                'string',
                'max:100',
                Rule::unique('customers', 'username')->ignore($customerId),
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('customers', 'email')->ignore($customerId),
            ],
            'mobile_no' => [
                'required',
                'string',
                'max:30',
                Rule::unique('customers', 'mobile_no')->ignore($customerId),
            ],
            'nationality' => ['required', 'string', 'max:100'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', 'integer'],
            'location_id' => ['required', 'integer', 'min:1'],
            'street' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:30'],
            'password' => $customer
                ? ['nullable', 'string', 'min:8', 'confirmed']
                : ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'username.unique' => 'This username is already taken.',
            'email.unique' => 'This email address is already registered.',
            'mobile_no.unique' => 'This mobile number is already registered.',
        ])->validate();
    }

    private function mapLocalCustomerData(array $validated, ?array $speedCustomer = null, ?int $fallbackCustomerId = null, ?Customer $customer = null): array
    {
        $speedCustomer = $speedCustomer ?? [];
        $address = data_get($speedCustomer, 'address', []);

        return [
            'customer_id' => (int) (data_get($speedCustomer, 'id')
                ?? data_get($speedCustomer, 'customerId')
                ?? $fallbackCustomerId
                ?? $customer?->customer_id),
            'username' => $validated['username'],
            'first_name' => data_get($speedCustomer, 'firstName', $validated['first_name']),
            'last_name' => data_get($speedCustomer, 'lastName', $validated['last_name']),
            'mobile_no' => data_get($speedCustomer, 'mobileNo', $validated['mobile_no']),
            'email' => data_get($speedCustomer, 'email', $validated['email']),
            'gender' => data_get($speedCustomer, 'gender', $validated['gender']),
            'nationality' => data_get($speedCustomer, 'nationality', $validated['nationality']),
            'date_of_birth' => !empty(data_get($speedCustomer, 'dateOfBirth'))
                ? Carbon::parse(data_get($speedCustomer, 'dateOfBirth'))->format('Y-m-d')
                : Carbon::parse($validated['date_of_birth'])->format('Y-m-d'),
            'location_id' => data_get($speedCustomer, 'locationId', $validated['location_id']),
            'street' => data_get($address, 'addressLine1', $validated['street']),
            'city' => data_get($address, 'city', $validated['city']),
            'state' => data_get($address, 'state', $validated['state']),
            'country' => data_get($address, 'country', $validated['country']),
            'postal_code' => data_get($address, 'zipCode', $validated['postal_code']),
        ];
    }

    private function createSpeedCustomer(array $validated): array
    {
        $code = env('APP_CODE');
        $url = 'https://speedbookingportalapi.azurewebsites.net/api/CreateCustomer?code=' . urlencode($code);

        $payload = [
            'firstName' => $validated['first_name'],
            'lastName' => $validated['last_name'],
            'email' => $validated['email'],
            'mobileNo' => $validated['mobile_no'],
            'nationality' => $validated['nationality'],
            'dateOfBirth' => Carbon::parse($validated['date_of_birth'])->format('Y-m-d\TH:i:s'),
            'gender' => (int) $validated['gender'],
            'locationId' => (int) $validated['location_id'],
            'address' => [
                'addressLine1' => $validated['street'],
                'city' => $validated['city'],
                'country' => $validated['country'],
                'zipCode' => $validated['postal_code'],
                'state' => $validated['state'],
            ],
        ];

        return $this->speedPost($url, $payload);
    }

    private function updateSpeedCustomer(Request $request, $customerId, $code = null): array
    {
        $code = $code ?: env('APP_CODE');
        $url = 'https://speedbookingportalapi.azurewebsites.net/api/UpdateCustomer?code=' . urlencode($code);

        $payload = [
            'id' => (int) $customerId,
            'firstName' => $request->input('firstName', $request->input('first_name')),
            'lastName' => $request->input('lastName', $request->input('last_name')),
            'email' => $request->input('email'),
            'mobileNo' => $request->input('mobileNo', $request->input('mobile_no')),
            'nationality' => $request->input('nationality'),
            'dateOfBirth' => Carbon::parse($request->input('dateOfBirth', $request->input('date_of_birth')))->format('Y-m-d\TH:i:s'),
            'gender' => (int) $request->input('gender'),
            'locationId' => (int) $request->input('locationId', $request->input('location_id')),
            'address' => [
                'addressLine1' => $request->input('street', $request->input('addressLine1')),
                'city' => $request->input('city'),
                'country' => $request->input('country'),
                'zipCode' => $request->input('postalCode', $request->input('postal_code')),
                'state' => $request->input('state'),
            ],
        ];

        return $this->speedPost($url, $payload);
    }

    private function getCustomerById($customerId): array
    {
        $code = env('APP_CODE');
        $url = 'https://speedbookingportalapi.azurewebsites.net/api/GetCustomerById?code=' . urlencode($code);

        return $this->speedPost($url, [
            'Id' => (int) $customerId,
        ]);
    }

    private function extractSpeedResult(array $response): ?array
    {
        if (isset($response['result']) && is_array($response['result'])) {
            return $response['result'];
        }

        if (isset($response['result']) && !is_array($response['result']) && $response['result'] !== null) {
            return ['id' => $response['result']];
        }

        if (isset($response['id']) || isset($response['firstName']) || isset($response['customerId'])) {
            return $response;
        }

        return null;
    }

    private function resolveSpeedCustomerId($result): ?int
    {
        if (is_array($result)) {
            $candidate = $result['id'] ?? $result['customerId'] ?? $result['contactId'] ?? null;

            return is_numeric($candidate) ? (int) $candidate : null;
        }

        return is_numeric($result) ? (int) $result : null;
    }

    private function speedPost(string $url, array $payload): array
    {
        try {
            $response = Http::withoutVerifying()
                ->acceptJson()
                ->asJson()
                ->withHeaders([
                    'ApiKey' => env('API_Key'),
                ])
                ->timeout(60)
                ->connectTimeout(20)
                ->post($url, $payload);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'result' => null,
            ];
        }

        $decoded = $response->json();

        if ($response->failed()) {
            return [
                'success' => false,
                'error' => is_array($decoded)
                    ? ($decoded['error'] ?? $decoded['message'] ?? 'Speed API request failed')
                    : 'Speed API request failed',
                'result' => $decoded ?? $response->body(),
            ];
        }

        if (!is_array($decoded)) {
            return [
                'success' => false,
                'error' => 'Invalid JSON response received from Speed system.',
                'result' => $response->body(),
            ];
        }

        return $decoded;
    }
}
