<?php

namespace App\Http\Controllers\APIs;

use App\Http\Requests\Api\InquiryRequest;
use App\Http\Resources\InquiryResource;
use App\Mail\InquiryConfirmationMail;
use App\Models\Inquiry;
use App\Models\Booking;
use App\Support\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

class InquiryController extends BaseApiController
{
    private const ADMIN_RECIPIENTS = [
        'tahreem@falcondrive.ae',
        'sales@falcondrive.ae',
        'abbas@quickdigitals.ae'
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

            if (!$this->guardAgainstSpam($request, $data)) {
                return $this->errorResponse(
                    'Spam request detected. Inquiry was not submitted.',
                    [],
                    422
                );
            }

            $record = Inquiry::create($data);

            // Lead Webhook
            // $webhookResponse = $this->sendLeadWebhook($record);

            // Emails
            $this->sendInquiryEmails($record);

            // Admin Notification
            AdminNotificationService::notifyInquiry($record, 'created');

            return $this->successResponse(
                $this->storeMessage,
                [
                    'inquiry' => InquiryResource::make($record)->resolve(),
                    // 'webhook' => $webhookResponse,
                ],
                201
            );

        } catch (ValidationException $e) {

            return $this->errorResponse(
                'Validation failed',
                ['errors' => $e->errors()],
                422
            );

        } catch (Throwable $e) {

            return $this->errorResponse(
                $e->getMessage(),
                ['exception' => class_basename($e)],
                500
            );
        }
    }

    private function sendLeadWebhook(Inquiry $record): array
    {
        try {

            $webhookUrl = config('services.lead_webhook.url');

            if (blank($webhookUrl)) {
                return [
                    'status' => false,
                    'message' => 'Webhook URL not configured',
                ];
            }

            $payload = [
                'Name' => $record->name,
                'Phone' => $record->number,
                'Email' => $record->email,
                'Interested Car' => $record->car_name,
            ];

            $response = Http::timeout(15)
                ->acceptJson()
                ->asJson()
                ->post($webhookUrl, $payload);

            return [
                'status' => $response->successful(),
                'status_code' => $response->status(),
                'webhook_url' => $webhookUrl,
                'payload' => $payload,
                'response' => $response->json() ?: $response->body(),
            ];

        } catch (Throwable $webhookException) {

            report($webhookException);

            return [
                'status' => false,
                'message' => $webhookException->getMessage(),
                'webhook_url' => config('services.lead_webhook.url'),
            ];
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

        try {
            Mail::to(self::ADMIN_RECIPIENTS)->send(new InquiryConfirmationMail($record, 'admin'));
        } catch (Throwable $mailException) {
            report($mailException);
        }
    }

    private function guardAgainstSpam(Request $request, array $data): bool
    {
        if (blank($request->userAgent())) {
            return false;
        }

        if (!$this->verifyBotProtection($request)) {
            return false;
        }

        $recentDuplicate = Inquiry::query()
            ->where('number', $data['number'])
            ->when(!empty($data['email']), fn ($query) => $query->where('email', $data['email']))
            ->when(!empty($data['message']), fn ($query) => $query->where('message', $data['message']))
            ->when(!empty($data['car_name']), fn ($query) => $query->where('car_name', $data['car_name']))
            ->where('created_at', '>=', Carbon::now()->subMinutes(10))
            ->exists();

        if ($recentDuplicate) {
            return false;
        }

        return true;
    }

    private function verifyBotProtection(Request $request): bool
    {
        $recaptchaSecret = (string) config('services.recaptcha.secret');

        if ($recaptchaSecret === '') {
            return true;
        }

        $token = (string) $request->input('g-recaptcha-response', '');

        if ($token === '') {
            return false;
        }

        $response = Http::asForm()
            ->timeout(10)
            ->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $recaptchaSecret,
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);

        return $response->ok() 
            && data_get($response->json(), 'success') === true;
    }

}
