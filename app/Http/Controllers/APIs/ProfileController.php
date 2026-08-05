<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use App\Models\Customer;
use Carbon\Carbon;

class ProfileController extends Controller
{
    
    /**
     * Get authenticated user profile.
     */
    public function profile(Request $request)
    {
        try {
            $userId = $request->auth_user_id;

            $user = Customer::find($userId);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Profile fetched successfully',
                'data' => $this->profileResponse($user)
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Update authenticated user profile
     * in Speed system and local database.
     */
    public function updateProfile(Request $request)
    {
        $userId = $request->auth_user_id;

        $user = Customer::find($userId);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
                'data' => []
            ], 404);
        }

        if (empty($user->customer_id)) {
            return response()->json([
                'status' => false,
                'message' => 'Speed customer ID not found for this user',
                'data' => []
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'username' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('customers', 'username')->ignore($user->id),
            ],
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                Rule::unique('customers', 'email')->ignore($user->id),
            ],
            'mobile_no' => [
                'required',
                'string',
                'max:30',
                Rule::unique('customers', 'mobile_no')->ignore($user->id),
            ],
            'gender' => 'required|integer',
            'nationality' => 'required|string|max:100',
            'date_of_birth' => 'required|date',
            'location_id' => 'required|integer',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:30'
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
            $code = env('APP_CODE');

            /*
            |--------------------------------------------------------------------------
            | STEP 2: Fetch latest customer data from Speed
            |--------------------------------------------------------------------------
            */
            $customerDataResponse = $this->getCustomerById(
                $user->customer_id,
                $code
            );

            if (
                empty($customerDataResponse['success']) ||
                empty($customerDataResponse['result'])
            ) {
                return response()->json([
                    'status' => false,
                    'message' => 'Customer was updated in Speed, but updated customer data could not be fetched',
                    'data' => $customerDataResponse
                ], 400);
            }

            $customer = $customerDataResponse['result'];

            /*
            |--------------------------------------------------------------------------
            | STEP 3: Update local user from Speed response
            |--------------------------------------------------------------------------
            */
            $user->update([
                'customer_id' => $customer['id'] ?? $user->customer_id,

                'first_name' => $customer['firstName']
                    ?? $request->first_name,

                'last_name' => $customer['lastName']
                    ?? $request->last_name,

                'gender' => $customer['gender']
                    ?? $request->gender,

                'nationality' => $customer['nationality']
                    ?? $request->nationality,

                'date_of_birth' => !empty($customer['dateOfBirth'])
                    ? Carbon::parse($customer['dateOfBirth'])->format('Y-m-d')
                    : Carbon::parse($request->date_of_birth)->format('Y-m-d'),

                'location_id' => $customer['locationId']
                    ?? $request->location_id,

                'street' => data_get(
                    $customer,
                    'address.addressLine1',
                    $request->street
                ),

                'city' => data_get(
                    $customer,
                    'address.city',
                    $request->city
                ),

                'state' => data_get(
                    $customer,
                    'address.state',
                    $request->state
                ),

                'country' => data_get(
                    $customer,
                    'address.country',
                    $request->country
                ),

                'postal_code' => data_get(
                    $customer,
                    'address.zipCode',
                    $request->postal_code
                ),

                'username' => $request->has('username')
                    ? $request->username
                    : $user->username,

                'email' => $customer['email']
                    ?? $request->email,

                'mobile_no' => $customer['mobileNo']
                    ?? $request->mobile_no
            ]);

            $user->refresh();

            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully',
                'data' => $this->profileResponse($user)
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Update customer in Speed system.
     */
    private function updateSpeedCustomer(
        Request $request,
        $customerId,
        $code
    ) {
        $url = "https://speedbookingportalapi.azurewebsites.net/api/UpdateCustomer?code="
            . urlencode($code);

        $payload = [
            'id' => (int) $customerId,
            'firstName' => $request->first_name,
            'lastName' => $request->last_name,
            'email' => $request->email,
            'mobileNo' => $request->mobile_no,
            'nationality' => $request->nationality,

            // Example: 1999-09-10T00:00:00
            'dateOfBirth' => Carbon::parse($request->date_of_birth)
                ->startOfDay()
                ->format('Y-m-d\TH:i:s'),

            'gender' => (int) $request->gender,
            'locationId' => (int) $request->location_id,

            'address' => [
                'addressLine1' => $request->street,
                'city' => $request->city,
                'country' => $request->country,
                'zipCode' => $request->postal_code,
                'state' => $request->state
            ]
        ];

        return $this->curlPost($url, $payload);
    }

    /**
     * Fetch customer from Speed system.
     */
    private function getCustomerById($customerId, $code)
    {
        $url = "https://speedbookingportalapi.azurewebsites.net/api/GetCustomerById?code="
            . urlencode($code);

        $payload = [
            'Id' => (int) $customerId
        ];

        return $this->curlPost($url, $payload);
    }

    /**
     * Common Speed API POST request.
     */
    private function curlPost($url, array $payload)
    {
        $apiKey = env('API_Key');

        try {
            $response = Http::withoutVerifying()
                ->acceptJson()
                ->asJson()
                ->withHeaders([
                    'ApiKey' => $apiKey,
                ])
                ->timeout(60)
                ->connectTimeout(20)
                ->post($url, $payload);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'result' => null
            ];
        }

        $decodedResponse = $response->json();

        if ($response->failed()) {
            return [
                'success' => false,
                'error' => is_array($decodedResponse)
                    ? (
                        $decodedResponse['error']
                        ?? $decodedResponse['message']
                        ?? 'Speed API request failed'
                    )
                    : 'Speed API request failed',
                'result' => $decodedResponse ?? $response->body()
            ];
        }

        if (!is_array($decodedResponse)) {
            return [
                'success' => false,
                'error' => 'Invalid JSON response received from Speed system',
                'result' => $response->body()
            ];
        }

        return $decodedResponse;
    }

    /**
     * Common profile response.
     */
    private function profileResponse(Customer $user)
    {
        return [
            'user_id' => $user->id,
            'customer_id' => $user->customer_id,
            'username' => $user->username,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'gender' => $user->gender,
            'nationality' => $user->nationality,
            'date_of_birth' => $user->date_of_birth,
            'location_id' => $user->location_id,
            'street' => $user->street,
            'city' => $user->city,
            'state' => $user->state,
            'country' => $user->country,
            'postal_code' => $user->postal_code,
            'mobile_no' => $user->mobile_no,
            'email' => $user->email,
            'permissions' => !empty($user->permissions)
                ? explode(',', $user->permissions)
                : []
        ];
    }
}
