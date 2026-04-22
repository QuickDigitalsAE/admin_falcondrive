<?php

namespace App\Http\Controllers\APIs;

use App\Http\Requests\Api\InquiryRequest;
use App\Http\Resources\InquiryResource;
use App\Mail\InquiryConfirmationMail;
use App\Models\Inquiry;
use App\Support\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Illuminate\Validation\ValidationException;

class InquiryController extends BaseApiController
{
    private const ADMIN_RECIPIENTS = [
        'tahreem@falcondrive.ae',
        'sales@falcondrive.ae',
    ];

    protected string $modelClass = Inquiry::class;
    protected string $resourceClass = InquiryResource::class;
    protected string $storeRequestClass = InquiryRequest::class;
    protected string $updateRequestClass = InquiryRequest::class;
    protected array $searchable = ['name', 'number', 'email', 'car_name'];
    protected array $with = [];
    protected string $publicMessage = 'Inquiry list fetched successfully';
    protected string $singleMessage = 'Inquiry fetched successfully';
    protected string $storeMessage = 'Inquiry created successfully';
    protected string $updateMessage = 'Inquiry updated successfully';
    protected string $deleteMessage = 'Inquiry deleted successfully';

    public function storePublic(Request $request)
    {
        try {
            $formRequest = new InquiryRequest();
            $request->validate($formRequest->rules());
            $data = $formRequest->sanitize($request);
            $this->guardAgainstSpam($request, $data);
            $record = Inquiry::create($data);

            $this->sendInquiryEmails($record);
            AdminNotificationService::notifyInquiry($record, 'created');

            return $this->successResponse($this->storeMessage, InquiryResource::make($record)->resolve(), 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', ['errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            return $this->errorResponse($e->getMessage(), ['exception' => class_basename($e)], 500);
        }
    }

    private function sendInquiryEmails(Inquiry $record): void
    {
        if (!empty($record->email)) {
            try {
                Mail::to($record->email)->send(new InquiryConfirmationMail($record, 'client'));
            } catch (Throwable $mailException) {
                report($mailException);
            }
        }

        // try {
        //     Mail::to(self::ADMIN_RECIPIENTS)->send(new InquiryConfirmationMail($record, 'admin'));
        // } catch (Throwable $mailException) {
        //     report($mailException);
        // }
    }

    private function guardAgainstSpam(Request $request, array $data): void
    {
        if (blank($request->userAgent())) {
            throw ValidationException::withMessages([
                'request' => ['Invalid inquiry request.'],
            ]);
        }

        $this->verifyBotProtection($request);

        $recentDuplicate = Inquiry::query()
            ->where('number', $data['number'])
            ->when(!empty($data['email']), fn ($query) => $query->where('email', $data['email']))
            ->when(!empty($data['message']), fn ($query) => $query->where('message', $data['message']))
            ->when(!empty($data['car_name']), fn ($query) => $query->where('car_name', $data['car_name']))
            ->where('created_at', '>=', Carbon::now()->subMinutes(10))
            ->exists();

        if ($recentDuplicate) {
            throw ValidationException::withMessages([
                'request' => ['Duplicate inquiry detected. Please wait before submitting again.'],
            ]);
        }
    }

    private function verifyBotProtection(Request $request): void
    {
        $recaptchaSecret = (string) config('services.recaptcha.secret');

        if ($recaptchaSecret !== '') {
            $token = (string) $request->input('g-recaptcha-response', '');

            if ($token === '') {
                throw ValidationException::withMessages([
                    'g-recaptcha-response' => ['reCAPTCHA verification is required!'],
                ]);
            }

            $response = Http::asForm()
                ->timeout(10)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $recaptchaSecret,
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ]);

            if (!$response->ok() || !data_get($response->json(), 'success')) {
                throw ValidationException::withMessages([
                    'g-recaptcha-response' => ['reCAPTCHA verification failed.'],
                ]);
            }
        }
    }

    public function getCustomerDetailByEmailOrMobileNo(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
                'result' => null
            ], 400);
        }

        try {
            $code = env('APP_CODE');
            $email = $request->email;

            $url = "https://speedbookingapitest.azurewebsites.net/api/GetCustomerDetailByEmailOrMobileNo?code=" . urlencode($code);

            $response = Http::withHeaders([
                'ApiKey' => env('API_Key'),
                'Accept' => 'application/json'
            ])->post($url, [
                'Email' => $email
            ]);

            // API fail
            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'error' => 'API request failed',
                    'result' => $response->body()
                ], $response->status());
            }

            $data = $response->json();

            // Empty response
            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No data found',
                    'result' => null
                ]);
            }

            // 🔥 FIX: unwrap external API response
            $finalResult = $data['result'] ?? null;

            return response()->json([
                'success' => $data['success'] ?? true,
                'error' => $data['error'] ?? null,
                'result' => $finalResult
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'result' => null
            ], 500);
        }
    }

    public function getVehicles()
    {
        $code = env('APP_CODE');
        $url = "https://speedbookingapitest.azurewebsites.net/api/GetVehicles";

        $page = 1;
        $allItems = [];

        do {

            $payload = [
                'PageNumber' => $page,
            ];

            $response = Http::withHeaders([
                'ApiKey' => env('API_Key'),
                'Content-Type' => 'application/json'
            ])->post($url . '?code=' . $code, $payload);

            $data = $response->json();

            $items = $data['result']['items'] ?? [];

            // merge items
            $allItems = array_merge($allItems, $items);

            $page++;

        } while (!empty($items)); // jab tak items milte rahen

        return [
            'totalCount' => count($allItems),
            'items' => $allItems
        ];
    }

    public function GetVehicleGroups()
    {
        $code = env('APP_CODE');
        $url = "https://speedbookingapitest.azurewebsites.net/api/GetVehicleGroups";

        $page = 1;
        $allItems = [];

        do {

            $payload = [
                'PageNumber' => $page,
            ];

            $response = Http::withHeaders([
                'ApiKey' => env('API_Key'),
                'Content-Type' => 'application/json'
            ])->post($url.'?code='.$code, $payload);

            $data = $response->json();

            $items = $data['result']['items'] ?? [];

            // merge items
            $allItems = array_merge($allItems, $items);

            $page++;

        } while (!empty($items)); // jab tak items milte rahen

        return [
            'totalCount' => count($allItems),
            'items' => $allItems
        ];
    }

    public function GetLocations()
    {
        $code = env('APP_CODE');
        $url = "https://speedbookingapitest.azurewebsites.net/api/GetLocations";

        $page = 1;
        $allItems = [];

        do {
            $payload = [
                'PageNumber' => $page,
            ];

            $response = Http::withHeaders([
                'ApiKey' => env('API_Key'),
                'Content-Type' => 'application/json'
            ])->post($url . '?code=' . $code, $payload);

            $data = $response->json();

            $items = $data['result'] ?? [];

            $allItems = array_merge($allItems, $items);

            $hasNext = $data['hasNext'] ?? false; // safe check
            $page++;

        } while ($hasNext);

        return [
            'totalCount' => count($allItems),
            'items' => $allItems
        ];
    }

    public function GetChargesSettings()
    {
        $code = env('APP_CODE');
        $url = "https://speedbookingapitest.azurewebsites.net/api/GetChargesSettings";

        $page = 1;
        $allItems = [];

        do {

            $payload = [
                'Module' => $page,
            ];

            $response = Http::withHeaders([
                'ApiKey' => env('API_Key'),
                'Content-Type' => 'application/json'
            ])->post($url.'?code='.$code, $payload);

            $data = $response->json();

            $items = $data['result']['items'] ?? [];

            // merge items
            $allItems = array_merge($allItems, $items);

            $page++;

        } while (!empty($items)); // jab tak items milte rahen

        return [
            'totalCount' => count($allItems),
            'items' => $allItems
        ];
    }

    public function createCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'email' => 'required|email',
            'mobileNo' => 'required|string',
            'locationId' => 'required|integer',
            'street' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postalCode' => 'required|string',
            'country' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
                'result' => null
            ], 400);
        }

        try {
            $code = env('APP_CODE');

            $url = "https://speedbookingapitest.azurewebsites.net/api/CreateCustomer?code=" . urlencode($code);

            $payload = [
                "firstName" => $request->firstName,
                "lastName" => $request->lastName,
                "email" => $request->email,
                "mobileNo" => $request->mobileNo,
                "locationId" => (int) $request->locationId,
                "address" => [
                    "street" => $request->street,
                    "city" => $request->city,
                    "state" => $request->state,
                    "postalCode" => $request->postalCode,
                    "country" => $request->country
                ]
            ];

            $response = Http::withHeaders([
                'ApiKey' => env('API_Key'),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post($url, $payload);

            // API fail
            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'error' => 'API request failed',
                    'result' => $response->body()
                ], $response->status());
            }

            $data = $response->json();

            return response()->json([
                'success' => $data['success'] ?? true,
                'error' => $data['error'] ?? null,
                'result' => $data['result'] ?? $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'result' => null
            ], 500);
        }
    }

    public function createBooking(Request $request)
    {
        $code = env('APP_CODE');
        $url = "https://speedbookingapitest.azurewebsites.net/api/CreateBooking";

        $inquiry = Inquiry::find($request->inquiry_id);

        if (!$inquiry) {
            return response()->json([
                'success' => false,
                'error' => 'Inquiry not found'
            ]);
        }

        $startDate = Carbon::parse($inquiry->from_date)->toISOString();
        $endDate = Carbon::parse($inquiry->to_date)->toISOString();

        // Charges array (agar dynamic hai)
        $charges = json_decode($request->charges_json, true) ?? [];

        $payload = [
            "booking" => [
                "tariffGroupId" => (int)$request->tariffGroupId,
                "startDate" => $startDate,
                "endDate" => $endDate,

                "bookingStatus" => (int)$request->bookingStatus,
                "advance" => (float)$request->advance,
                "locationId" => (int)$request->locationId,
                "notes" => $request->notes,

                "vehicle" => [
                    "tariffGroupId" => (int)$request->tariffGroupId,
                    "plateNo" => $request->plateNo,
                    "tariffGroup" => [
                        "Id" => (int)$request->tariffGroupId,
                        "AcrissCategory" => null,
                        "AcrissFuelAc" => null,
                        "AcrissType" => null,
                        "AcrissTransDrive" => null,
                        "Title" => $request->vehicleTitle,
                        "SubTitle" => "4 doors, 5 seats",
                        "PassengerCapacity" => 5,
                        "LargeBagsCapacity" => 2,
                        "SmallBagsCapacity" => 2,
                        "SkipBookingGatewayPayment" => false,
                        "DisplayImage" => null,
                        "VehicleGroupId" => null
                    ]
                ],

                "taxPercent" => (float)$request->taxPercent,
                "charges" => $charges,

                "discount" => (float)$request->discount,
                "tax" => (float)$request->chargesTax,
                "totalCharges" => (float)$request->totalCharges,

                "bookingType" => (int)$request->bookingType,
                "customerId" => (int)$request->customerId,

                "customer" => [
                    "firstName" => $request->firstName,
                    "lastName" => $request->lastName,
                    "email" => $request->customerEmail,
                    "mobileNo" => $request->mobileNo,
                    "locationId" => 1,
                    "address" => [
                        "street" => $request->street,
                        "city" => $request->city,
                        "state" => $request->state,
                        "postalCode" => $request->postalCode,
                        "country" => $request->country
                    ]
                ],

                "BillingDetail" => [
                    "Notes" => $request->billingNotes,
                    "CreditCard" => [
                        "ContactCardsId" => 0,
                        "CardNoLastDigits" => $request->cardLastDigits,
                        "CardHolderName" => null,
                        "TransactionNo" => $request->transactionNo,
                        "ExpiryDate" => $request->expiryDate,
                        "CommissionPercentage" => (float)$request->commissionPercentage,
                        "ContactCard" => [
                            "Type" => 1,
                            "CardNo" => $request->cardNumber,
                            "CardNoLastFourDigits" => $request->cardLastFourDigits,
                            "Expiry" => $request->cardExpiry,
                            "Cvv" => $request->cvv,
                            "NameOnCard" => $request->nameOnCard,
                            "BankName" => $request->bankName,
                            "IsDefault" =>  true,
                            "ContactId" => (int)$request->customerId,
                            "Contact" => null,
                            "ExternalSource" => 0
                        ]
                    ]
                ],

                "amount" => (float)$request->amount,
                "skipBookingGatewayPayment" => false,
                "currency" => "AED"
            ]
        ];

        $response = Http::withHeaders([
            'ApiKey' => env('API_Key'),
            'Content-Type' => 'application/json'
        ])->post($url . '?code=' . $code, $payload);

        $responseData = $response->json();

        // SUCCESS CASE
        if ($response->successful() && ($responseData['success'] ?? false)) {

            // booking id extract (adjust if structure different)
            $bookingId = $responseData['result']['id'] ?? null;

            // Save in DB
            $inquiry->update([
                'send_booking_id' => $bookingId,
                'form_payload' => json_encode($responseData['result']) // or serialize($payload)
            ]);

        }


        return response()->json([
            'success' => $responseData['success'] ?? true,
            'error' => $responseData['error'] ?? null,
            'result' => $responseData['result'] ?? null
        ]);
    }

}
