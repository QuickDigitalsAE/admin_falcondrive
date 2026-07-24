<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\BlacklistedToken;
use App\Helpers\JwtHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CustomerController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
                'data' => []
            ], 422);
        }

        try {
            $login = trim($request->login);

            /*
            |--------------------------------------------------------------------------
            | STEP 1: Check customer in local customers table
            |--------------------------------------------------------------------------
            */
            $customerRecord = Customer::where('email', $login)
                ->orWhere('username', $login)
                ->orWhere('mobile_no', $login)
                ->first();

            /*
            |--------------------------------------------------------------------------
            | STEP 2: Local customers found - verify bcrypt password
            |--------------------------------------------------------------------------
            */
            if ($customerRecord) {
                if (!password_verify($request->password, $customerRecord->password)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Password does not match. Please reset your password through Forgot Password.',
                        'data' => [
                            'forgot_password_required' => true
                        ]
                    ], 401);
                }

                $token = JwtHelper::generateToken($customerRecord->id);

                return response()->json([
                    'status' => true,
                    'message' => 'Login successful',
                    'data' => [
                        'id' => $customerRecord->id,
                        'customer_id' => $customerRecord->customer_id,
                        'username' => $customerRecord->username,
                        'first_name' => $customerRecord->first_name,
                        'last_name' => $customerRecord->last_name,
                        'gender' => $customerRecord->gender,
                        'nationality' => $customerRecord->nationality,
                        'date_of_birth' => $customerRecord->date_of_birth,
                        'location_id' => $customerRecord->location_id,
                        'street' => $customerRecord->street,
                        'city' => $customerRecord->city,
                        'state' => $customerRecord->state,
                        'country' => $customerRecord->country,
                        'postal_code' => $customerRecord->postal_code,
                        'mobile_no' => $customerRecord->mobile_no,
                        'email' => $customerRecord->email,
                        'permissions' => !empty($customerRecord->permissions)
                            ? explode(',', $customerRecord->permissions)
                            : [],
                        'token' => $token
                    ]
                ], 200);
            }

            /*
            |--------------------------------------------------------------------------
            | STEP 3: customers not found locally
            | Speed system only supports email or mobile lookup
            |--------------------------------------------------------------------------
            */
            $email = null;
            $mobileNo = null;

            if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
                $email = $login;
            } else {
                $mobileNo = $login;
            }

            $code = env('APP_CODE');

            $customerIdResponse = $this->getCustomerId(
                $email,
                $mobileNo,
                $code
            );

            if (
                empty($customerIdResponse['success']) ||
                empty($customerIdResponse['result'])
            ) {
                return response()->json([
                    'status' => false,
                    'message' => 'No customer found with this email or mobile number.',
                    'data' => []
                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | STEP 4: Get customer ID from Speed response
            |--------------------------------------------------------------------------
            */
            $customerResult = $customerIdResponse['result'];

            if (is_array($customerResult)) {
                $customerId = $customerResult['id']
                    ?? $customerResult['customerId']
                    ?? $customerResult['contactId']
                    ?? null;
            } else {
                $customerId = $customerResult;
            }

            if (empty($customerId)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Customer ID was not found in the Speed system response.',
                    'data' => $customerIdResponse
                ], 400);
            }

            /*
            |--------------------------------------------------------------------------
            | STEP 5: Check again by customer ID
            |--------------------------------------------------------------------------
            */
            // $customer = Customer::where('customer_id', $customerId)->first();

            // if ($customer) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Password does not match. Please reset your password through Forgot Password.',
            //         'data' => [
            //             'id' => $customer->id,
            //             'customer_id' => $customer->customer_id,
            //             'email' => $customer->email,
            //             'mobile_no' => $customer->mobile_no,
            //             'forgot_password_required' => true
            //         ]
            //     ], 401);
            // }

            /*
            |--------------------------------------------------------------------------
            | STEP 6: Fetch full customer data from Speed
            |--------------------------------------------------------------------------
            */
            $customerDataResponse = $this->getCustomerById(
                $customerId,
                $code
            );

            if (
                empty($customerDataResponse['success']) ||
                empty($customerDataResponse['result'])
            ) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to fetch customer data from the Speed system.',
                    'data' => $customerDataResponse
                ], 400);
            }

            $customer = $customerDataResponse['result'];

            /*
            |--------------------------------------------------------------------------
            | STEP 7: Check duplicate email/mobile before local insertion
            |--------------------------------------------------------------------------
            */
            $customerEmail = $customer['email'] ?? $email;
            $customerMobile = $customer['mobileNo'] ?? $mobileNo;

            $duplicateUser = Customer::where(function ($query) use (
                $customerId,
                $customerEmail,
                $customerMobile
            ) {
                $query->where('customer_id', $customerId);

                if (!empty($customerEmail)) {
                    $query->orWhere('email', $customerEmail);
                }

                if (!empty($customerMobile)) {
                    $query->orWhere('mobile_no', $customerMobile);
                }
            })->first();

            if ($duplicateUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Password does not match. Please reset your password through Forgot Password.',
                    'data' => [
                        'id' => $duplicateUser->id,
                        'customer_id' => $duplicateUser->customer_id,
                        'email' => $duplicateUser->email,
                        'mobile_no' => $duplicateUser->mobile_no
                    ]
                ], 401);
            }

            /*
            |--------------------------------------------------------------------------
            | STEP 8: Generate unique username
            |--------------------------------------------------------------------------
            */
            $baseUsername = null;

            if (!empty($customerEmail)) {
                $baseUsername = Str::slug(
                    Str::before($customerEmail, '@'),
                    '_'
                );
            }

            if (empty($baseUsername)) {
                $fullName = trim(
                    ($customer['firstName'] ?? '') . ' ' .
                    ($customer['lastName'] ?? '')
                );

                $baseUsername = Str::slug($fullName, '_');
            }

            if (empty($baseUsername)) {
                $baseUsername = 'customer_' . $customerId;
            }

            $baseUsername = substr($baseUsername, 0, 40);
            $generatedUsername = $baseUsername;
            $counter = 1;

            while (Customer::where('username', $generatedUsername)->exists()) {
                $generatedUsername = $baseUsername . '_' . $counter;
                $counter++;
            }

            /*
            |--------------------------------------------------------------------------
            | STEP 9: Create local customer
            |--------------------------------------------------------------------------
            | Speed system password is not available.
            | Random password is saved using bcrypt().
            |--------------------------------------------------------------------------
            */
            $permissions = [
                'All customers',
                'Documents',
                'All Invoices',
                'Salik Invoices',
                'All Bookings',
                'Booking Details',
                'Statement of Accounts',
                'Change Password',
                'Settings',
            ];

            $customerPassword = $request->password ?? null;
            
            $customerPayload = Customer::create([
                'customer_id' => $customer['id'] ?? $customerId,
                'first_name' => $customer['firstName'] ?? null,
                'last_name' => $customer['lastName'] ?? null,
                'gender' => $customer['gender'] ?? null,
                'nationality' => $customer['nationality'] ?? null,

                'date_of_birth' => !empty($customer['dateOfBirth'])
                    ? Carbon::parse($customer['dateOfBirth'])->format('Y-m-d')
                    : null,

                'location_id' => $customer['locationId'] ?? null,

                'street' => data_get($customer, 'address.addressLine1'),
                'city' => data_get($customer, 'address.city'),
                'state' => data_get($customer, 'address.state'),
                'country' => data_get($customer, 'address.country'),
                'postal_code' => data_get($customer, 'address.zipCode'),

                'username' => $generatedUsername,
                'email' => $customerEmail,
                'mobile_no' => $customerMobile,

                // Password saved through bcrypt()
                'password' => bcrypt($customerPassword),

                'permissions' => implode(',', $permissions)
            ]);

            $token = JwtHelper::generateToken($customerPayload->id);
            
            return response()->json([
                    'status' => true,
                    'message' => 'Login successful',
                    'data' => [
                        'id' => $customerPayload->id,
                        'customer_id' => $customerPayload->customer_id,
                        'username' => $customerPayload->username,
                        'first_name' => $customerPayload->first_name,
                        'last_name' => $customerPayload->last_name,
                        'gender' => $customerPayload->gender,
                        'nationality' => $customerPayload->nationality,
                        'date_of_birth' => $customerPayload->date_of_birth,
                        'location_id' => $customerPayload->location_id,
                        'street' => $customerPayload->street,
                        'city' => $customerPayload->city,
                        'state' => $customerPayload->state,
                        'country' => $customerPayload->country,
                        'postal_code' => $customerPayload->postal_code,
                        'mobile_no' => $customerPayload->mobile_no,
                        'email' => $customerPayload->email,
                        'permissions' => !empty($customerPayload->permissions)
                            ? explode(',', $customerPayload->permissions)
                            : [],
                        'token' => $token
                    ]
                ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username'   => 'required|unique:customers,username',
            'firstName'  => 'required|string',
            'lastName'   => 'required|string',
            'email'      => 'required|email',
            'password'   => 'required|min:6',
            'mobile_no'  => 'required|string',
            'permissions'=> 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $code = env('APP_CODE');
        $email = $request->email;
        $mobileNo = $request->mobile_no;

        // STEP 1: Get customer id from Speed
        $customerIdResponse = $this->getCustomerId($email, $mobileNo, $code);

        $customerId = null;

        if (!empty($customerIdResponse['success']) && !empty($customerIdResponse['result'])) {
            $customerId = $customerIdResponse['result'];
        }

        // STEP 2: If customer not found, create customer in Speed
        if (empty($customerId)) {
            $createCustomerResponse = $this->createSpeedCustomer($request, $code);

            if (empty($createCustomerResponse['success']) || empty($createCustomerResponse['result'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to create customer in Speed system',
                    'data' => $createCustomerResponse
                ], 400);
            }

            $createdCustomer = $createCustomerResponse['result'];

            $customerId = $createdCustomer['id']
                ?? $createdCustomer['customerId']
                ?? $createdCustomer;

            if (empty($customerId)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Customer created but customer id not found from Speed response',
                    'data' => $createCustomerResponse
                ], 400);
            }
        }

        // STEP 3: Check local customer already exists
        $existingCustomer = Customer::where('customer_id', $customerId)
            ->orWhere('email', $email)
            ->orWhere('mobile_no', $mobileNo)
            ->first();

        if ($existingCustomer) {
            return response()->json([
                'status' => false,
                'message' => 'A customer with this email address or mobile number is already registered. Please log in.',
                'data' => []
            ], 409);
        }

        // STEP 4: Fetch full customer data from Speed
        $customerDataResponse = $this->getCustomerById($customerId, $code);

        if (empty($customerDataResponse['success']) || empty($customerDataResponse['result'])) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch customer data',
                'data' => $customerDataResponse
            ], 400);
        }

        $customer = $customerDataResponse['result'];

        $permissions = !empty($request->permissions)
            ? implode(',', $request->permissions)
            : null;

        // STEP 5: Insert customer from Speed customer data
        $customer = Customer::create([
            'customer_id' => $customer['id'] ?? $customerId,

            'first_name' => $customer['firstName'] ?? null,
            'last_name' => $customer['lastName'] ?? null,
            'gender' => $customer['gender'] ?? null,
            'nationality' => $customer['nationality'] ?? null,
            'date_of_birth' => !empty($customer['dateOfBirth'])
                ? Carbon::parse($customer['dateOfBirth'])->format('Y-m-d')
                : null,
            'location_id' => $customer['locationId'] ?? null,

            'street' => $customer['address']['addressLine1'] ?? null,
            'city' => $customer['address']['city'] ?? null,
            'state' => $customer['address']['state'] ?? null,
            'country' => $customer['address']['country'] ?? null,
            'postal_code' => $customer['address']['zipCode'] ?? null,

            'username' => $request->username,
            'email' => $customer['email'] ?? $request->email,
            'mobile_no' => $customer['mobileNo'] ?? $request->mobile_no,
            'password' => bcrypt($request->password),
            'permissions' => $permissions
        ]);

        $token = JwtHelper::generateToken($customer->id);

        return response()->json([
            'status' => true,
            'message' => 'Customer registered successfully',
            'data' => $this->customerResponse($customer, $token)
        ]);
    }

    private function getCustomerId($email, $mobileNo, $code)
    {
        $url = "https://speedbookingportalapi.azurewebsites.net/api/GetContactIdByEmailOrMobileNo?code=" . urlencode($code);

        $payload = [
            'Email' => $email,
            'MobileNo' => $mobileNo
        ];

        return $this->curlPost($url, $payload);
    }

    private function getCustomerById($customerId, $code)
    {
        $url = "https://speedbookingportalapi.azurewebsites.net/api/GetCustomerById?code=" . urlencode($code);

        $payload = [
            'Id' => $customerId
        ];

        return $this->curlPost($url, $payload);
    }

    private function createSpeedCustomer(Request $request, $code)
    {
        $url = "https://speedbookingportalapi.azurewebsites.net/api/CreateCustomer?code=" . urlencode($code);

        /*
            Register request mein extra fields nahi aa rahi,
            is liye Speed customer create karne ke liye default values use ho rahi hain.
        */
        $payload = [
            "firstName"   => $request->firstName,
            "lastName"    => $request->lastName,
            "email" => $request->email,
            "mobileNo" => $request->mobile_no,
            "nationality" => "UAE",
            "dateOfBirth" => Carbon::parse("1990-01-01")->format('Y-m-d\TH:i:s'),
            "gender" => 1,
            "locationId" => 1,
            "address" => [
                "addressLine1" => "N/A",
                "city" => "Dubai",
                "country" => "UAE",
                "zipCode" => "N/A",
                "state" => "Dubai"
            ]
        ];

        return $this->curlPost($url, $payload);
    }

    private function updateSpeedCustomer(Request $request, $customerId, $code)
    {
        $url = "https://speedbookingportalapi.azurewebsites.net/api/UpdateCustomer?code=" . urlencode($code);

        $payload = [
            "id" => (int) $customerId,
            "firstName" => $request->input('firstName', $request->input('first_name')),
            "lastName" => $request->input('lastName', $request->input('last_name')),
            "email" => $request->input('email'),
            "mobileNo" => $request->input('mobileNo', $request->input('mobile_no')),
            "nationality" => $request->input('nationality'),
            "dateOfBirth" => Carbon::parse($request->input('dateOfBirth', $request->input('date_of_birth')))->format('Y-m-d\TH:i:s'),
            "gender" => (int) $request->input('gender'),
            "locationId" => (int) $request->input('locationId', $request->input('location_id')),
            "address" => [
                "addressLine1" => $request->input('street', $request->input('addressLine1')),
                "city" => $request->input('city'),
                "country" => $request->input('country'),
                "zipCode" => $request->input('postalCode', $request->input('postal_code')),
                "state" => $request->input('state')
            ]
        ];

        return $this->curlPost($url, $payload);
    }

    private function curlPost($url, $payload)
    {
        $apiKey = env('API_Key');

        try {
            $response = Http::withoutVerifying()
                ->acceptJson()
                ->asJson()
                ->withHeaders([
                    'ApiKey' => $apiKey,
                ])
                ->post($url, $payload);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'result' => null
            ];
        }

        $decoded = $response->json();

        if ($response->failed()) {
            return [
                'success' => false,
                'error' => 'API request failed',
                'result' => $decoded ?? $response
            ];
        }

        return $decoded ?? [
            'success' => false,
            'error' => 'Invalid JSON response',
            'result' => $response->body()
        ];
    }

    private function customerResponse($customer, $token)
    {
        return [
            'id' => $customer->id,
            'customer_id' => $customer->customer_id,
            'username' => $customer->username,
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'gender' => $customer->gender,
            'nationality' => $customer->nationality,
            'date_of_birth' => $customer->date_of_birth,
            'location_id' => $customer->location_id,
            'street' => $customer->street,
            'city' => $customer->city,
            'state' => $customer->state,
            'country' => $customer->country,
            'postal_code' => $customer->postal_code,
            'mobile_no' => $customer->mobile_no,
            'email' => $customer->email,
            'permissions' => !empty($customer->permissions) ? explode(',', $customer->permissions) : [],
            'token' => $token
        ];
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status' => false,
                'message' => 'Token not provided'
            ], 401);
        }

        BlacklistedToken::create([
            'token' => hash('sha256', $token)
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Logout successful'
        ]);
    }
}
