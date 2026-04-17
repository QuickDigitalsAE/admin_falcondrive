<?php

namespace App\Http\Controllers\APIs;

use App\Http\Requests\Api\InquiryRequest;
use App\Http\Resources\InquiryResource;
use App\Mail\InquiryConfirmationMail;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
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

        try {
            Mail::to(self::ADMIN_RECIPIENTS)->send(new InquiryConfirmationMail($record, 'admin'));
        } catch (Throwable $mailException) {
            report($mailException);
        }
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

}
