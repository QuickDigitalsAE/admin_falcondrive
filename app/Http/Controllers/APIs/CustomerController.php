<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\BlacklistedToken;
use App\Helpers\JwtHelper;
use App\Notifications\SendOtpNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CustomerController extends Controller
{
    public function login(Request $request)
    {
        // Email ko validate karne se pehle spaces remove karein
        $request->merge([
            'login' => strtolower(trim((string) $request->login))
        ]);

        $validator = Validator::make(
            $request->all(),
            [
                'login' => [
                    'required',
                    'string',
                    'email:rfc',
                    'max:255',
                ],
                'password' => [
                    'required',
                    'string',
                ],
            ],
            [
                'login.required' => 'Email address is required.',
                'login.email' => 'Please enter a valid email address.',
                'login.max' => 'Email address may not be greater than 255 characters.',
                'password.required' => 'Password is required.',
            ]
        );

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

            $customerRecord = Customer::where('email', $login)
                ->first();

            if ($customerRecord) {
                if (!Hash::check($request->password, $customerRecord->password)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Password does not match. Please reset your password through Forgot Password.',
                        'data' => [
                            'forgot_password_required' => true
                        ]
                    ], 401);
                }

                if (empty($customerRecord->email_verified_at)) {
                
                    $this->sendCustomerOtp($customerRecord);

                    return response()->json([
                        'status' => true,
                        'message' => 'Customer already exists but email is not verified. OTP resent to your email.',
                        'data' => []
                    ], 200);
                }

                $token = JwtHelper::generateToken($customerRecord->id);

                return response()->json([
                    'status' => true,
                    'message' => 'Login successful',
                    'data' => $this->customerResponse($customerRecord, $token)
                ], 200);
            }

            $email = null;
            $mobileNo = null;

            if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
                $email = $login;
            }

            $code = env('APP_CODE');

            $customerIdResponse = $this->getCustomerId($email, $mobileNo, $code);

            if (
                empty($customerIdResponse['success']) ||
                empty($customerIdResponse['result'])
            ) {
                return response()->json([
                    'status' => false,
                    'message' => 'No customer found with this email.',
                    'data' => []
                ], 404);
            }

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

            $customerDataResponse = $this->getCustomerById($customerId, $code);

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
            $customerEmail = $customer['email'] ?? $email;
            $customerMobile = $customer['mobileNo'] ?? $mobileNo;

            $duplicateUser = Customer::where(function ($query) use (
                $customerId,
                $customerEmail
            ) {
                $query->where('customer_id', $customerId);

                if (!empty($customerEmail)) {
                    $query->orWhere('email', $customerEmail);
                }
            })->first();

            if ($duplicateUser) {
                if (!Hash::check($request->password, $duplicateUser->password)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Password does not match. Please reset your password through Forgot Password.',
                        'data' => [
                            'forgot_password_required' => true,
                            'id' => $duplicateUser->id,
                            'customer_id' => $duplicateUser->customer_id,
                            'email' => $duplicateUser->email,
                            'mobile_no' => $duplicateUser->mobile_no
                        ]
                    ], 401);
                }

                $token = JwtHelper::generateToken($duplicateUser->id);

                return response()->json([
                    'status' => true,
                    'message' => 'Login successful',
                    'data' => $this->customerResponse($duplicateUser, $token)
                ], 200);
            }

            $baseUsername = null;

            if (!empty($customerEmail)) {
                $baseUsername = Str::slug(Str::before($customerEmail, '@'), '_');
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
                'password' => Hash::make($request->password),
                'permissions' => implode(',', $permissions),
                'email_verified_at' => null,
            ]);

            $this->sendCustomerOtp($customerPayload);

            return response()->json([
                'status' => true,
                'message' => 'Customer found in Speed. OTP sent to your email for verification.',
                'data' => $this->customerResponse($customerPayload, null)
            ], 201);
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

        try {
            $code = env('APP_CODE');
            $email = $request->email;
            $mobileNo = $request->mobile_no;

            $existingCustomer = Customer::where('email', $email)
                ->first();

            if ($existingCustomer) {
                return response()->json([
                    'status' => false,
                    'message' => 'A customer with this email address is already registered. Please log in.',
                    'data' => []
                ], 409);
            }

            $customerIdResponse = $this->getCustomerId($email, null, $code);

            $customerId = null;

            if (!empty($customerIdResponse['success']) && !empty($customerIdResponse['result'])) {
                $customerId = $customerIdResponse['result'];
            }

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

            $customerDataResponse = $this->getCustomerById($customerId, $code);

            if (empty($customerDataResponse['success']) || empty($customerDataResponse['result'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to fetch customer data',
                    'data' => $customerDataResponse
                ], 400);
            }

            $speedCustomer = $customerDataResponse['result'];
            $permissions = !empty($request->permissions)
                ? implode(',', $request->permissions)
                : null;

            $customer = Customer::create([
                'customer_id' => $speedCustomer['id'] ?? $customerId,
                'first_name' => $speedCustomer['firstName'] ?? null,
                'last_name' => $speedCustomer['lastName'] ?? null,
                'gender' => $speedCustomer['gender'] ?? null,
                'nationality' => $speedCustomer['nationality'] ?? null,
                'date_of_birth' => !empty($speedCustomer['dateOfBirth'])
                    ? Carbon::parse($speedCustomer['dateOfBirth'])->format('Y-m-d')
                    : null,
                'location_id' => $speedCustomer['locationId'] ?? null,
                'street' => data_get($speedCustomer, 'address.addressLine1'),
                'city' => data_get($speedCustomer, 'address.city'),
                'state' => data_get($speedCustomer, 'address.state'),
                'country' => data_get($speedCustomer, 'address.country'),
                'postal_code' => data_get($speedCustomer, 'address.zipCode'),
                'username' => $request->username,
                'email' => $speedCustomer['email'] ?? $request->email,
                'mobile_no' => $speedCustomer['mobileNo'] ?? $request->mobile_no,
                'password' => Hash::make($request->password),
                'permissions' => $permissions,
                'email_verified_at' => null,
            ]);

            $this->sendCustomerOtp($customer);

            return response()->json([
                'status' => true,
                'message' => 'Customer registered successfully. OTP sent to your email for verification.',
                'data' => $this->customerResponse($customer, null)
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function verifyEmailOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = Customer::where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP.',
                'data' => []
            ], 422);
        }

        if (empty($customer->otp_expires_at) || Carbon::now()->greaterThan($customer->otp_expires_at)) {
            return response()->json([
                'status' => false,
                'message' => 'OTP expired.',
                'data' => []
            ], 422);
        }

        $customer->email_verified_at = Carbon::now();
        $customer->otp = null;
        $customer->otp_expires_at = null;
        $customer->save();

        $token = JwtHelper::generateToken($customer->id);

        return response()->json([
            'status' => true,
            'message' => 'Email verified successfully.',
            'data' => $this->customerResponse($customer, $token)
        ], 200);
    }

    public function resendEmailOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Customer not found.',
                'data' => []
            ], 404);
        }

        if (!empty($customer->email_verified_at)) {
            return response()->json([
                'status' => false,
                'message' => 'Email is already verified.',
                'data' => $this->customerResponse($customer, null)
            ], 409);
        }

        $this->sendCustomerOtp($customer);

        return response()->json([
            'status' => true,
            'message' => 'OTP resent to your email.',
            'data' => $this->customerResponse($customer, null)
        ], 200);
    }

    private function sendCustomerOtp(Customer $customer): string
    {
        $otp = (string) random_int(100000, 999999);

        $customer->otp = $otp;
        $customer->otp_expires_at = Carbon::now()->addMinutes(5);
        $customer->save();

        $customer->notify(new SendOtpNotification($otp));

        return $otp;
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
            'email_verified_at' => $customer->email_verified_at,
            'email_verified' => !empty($customer->email_verified_at),
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
