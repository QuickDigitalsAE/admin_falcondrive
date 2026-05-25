<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public string $mailType = 'client'
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailType === 'admin'
                ? 'New Booking Received - ' . ($this->booking->name ?: 'Client')
                : 'Booking Confirmation - Falcon Drive'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-confirmation',
            with: [
                'isAdminMail' => $this->mailType === 'admin',
                'sections' => $this->buildSections(),
            ]
        );
    }

    private function buildSections(): array
    {
        return array_values(array_filter([
            $this->makeSection('Customer Details', [
                $this->makeField('Name', $this->booking->name),
                $this->makeField('Phone Number', $this->booking->number),
                $this->makeField('Email', $this->booking->email),
                $this->makeField('Resident / Tourist', $this->humanizeText($this->booking->resident_tourist)),
                $this->makeField('Contact Preference', $this->humanizeText($this->booking->contact_preference)),
            ]),
            $this->makeSection('Booking Schedule', [
                $this->makeField('Start Date', $this->formatDate($this->booking->start_date)),
                $this->makeField('Start Time', $this->formatTime($this->booking->start_time)),
                $this->makeField('End Date', $this->formatDate($this->booking->end_date)),
                $this->makeField('End Time', $this->formatTime($this->booking->end_time)),
                $this->makeField('Rental Type', $this->humanizeText($this->booking->rental_type)),
                $this->makeField('Rental Duration', $this->booking->rental_duration),
            ]),
            $this->makeSection('Rental Options', [
                $this->makeField('Full Insurance', $this->formatBoolean($this->booking->full_insurance)),
                $this->makeField('Additional Driver', $this->formatBoolean($this->booking->additional_driver)),
                $this->makeField('Baby Seat', $this->formatBoolean($this->booking->baby_seat)),
                $this->makeField('Deposit Waiver', $this->booking->deposit_waiver),
            ]),
            $this->makeSection('Pickup Details', [
                $this->makeField('Delivery Location', $this->booking->delivery_location),
                $this->makeField('Delivery Custom Address', $this->booking->delivery_custom_address),
                $this->makeField('Self Pickup Location', $this->booking->self_pickup_location),
                $this->makeField('Self Pickup Address', $this->booking->self_pickup_address),
            ]),
            $this->makeSection('Return Details', [
                $this->makeField('Return Location', $this->booking->return_location),
                $this->makeField('Return Custom Address', $this->booking->return_custom_address),
                $this->makeField('Self Return Location', $this->booking->self_return_location),
                $this->makeField('Self Return Address', $this->booking->self_return_address),
            ]),
            $this->makeSection('Pricing Details', [
                $this->makeMoneyField('Rental Price', $this->booking->rental_price),
                $this->makeMoneyField('Full Insurance Price', $this->booking->full_insurance_price),
                $this->makeMoneyField('Additional Driver Charges', $this->booking->additional_driver_charges),
                $this->makeMoneyField('Baby Seat Price', $this->booking->baby_seat_price),
                $this->makeMoneyField('Deposit Waiver Price', $this->booking->deposit_waiver_price),
                $this->makeMoneyField('Delivery Location Price', $this->booking->delivery_location_price),
                $this->makeMoneyField('Different City Dropoff Fee', $this->booking->different_city_dropoff_fee),
                $this->makeMoneyField('Return Location Price', $this->booking->return_location_price),
                $this->makeMoneyField('Coupon Amount', $this->booking->coupon_amount),
                $this->makeMoneyField('Pay Now Discount', $this->booking->pay_now_discount),
                $this->makeField('Coupon Code', $this->booking->coupon_code),
                $this->makeField('Discount Percentage', $this->formatPercentage($this->booking->discount_percentage)),
                $this->makeMoneyField('Subtotal', $this->booking->subtotal),
                $this->makeField('VAT Percentage', $this->formatPercentage($this->booking->vat_percentage)),
                $this->makeMoneyField('VAT Amount', $this->booking->vat_amount),
                $this->makeMoneyField('Total Amount', $this->booking->total_amount),
            ]),
            $this->makeSection('Payment Details', [
                $this->makeField('Payment Flow', $this->humanizeText($this->booking->payment_flow)),
                $this->makePaymentFlowMoneyField('Pay Now 20% To Reserve', $this->booking->{'pay_now_20%_to_Reserve'}),
                $this->makePaymentFlowMoneyField('Pay At Pickup 80%', $this->booking->{'pay_at_pickup_80%'}),
                $this->makeField('Paid ID', $this->booking->paid_id),
                $this->makeField('Paid Date', $this->formatDateTime($this->booking->paid_date)),
                $this->makeField('Paid Status', $this->booking->paid_status),
                $this->makeField('Paid Via', $this->booking->paid_via),
            ]),
            $this->makeSection('Terms Confirmation', [
                $this->makeField('22+ Years Confirmed', $this->formatBoolean($this->booking->term_22_years)),
                $this->makeField('6+ Months Experience Confirmed', $this->formatBoolean($this->booking->term_6_month_experience)),
            ]),
        ]));
    }

    private function makeSection(string $title, array $fields): ?array
    {
        $fields = array_values(array_filter($fields));

        if ($fields === []) {
            return null;
        }

        return [
            'title' => $title,
            'fields' => $fields,
        ];
    }

    private function makeField(string $label, mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        return [
            'label' => $label,
            'value' => trim((string) $value),
            'is_currency' => false,
        ];
    }

    private function makeMoneyField(string $label, mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        return [
            'label' => $label,
            'value' => $this->formatMoney($value),
            'is_currency' => true,
        ];
    }

    private function makePaymentFlowMoneyField(string $label, mixed $value): array
    {
        $paymentFlow = strtolower((string) $this->booking->payment_flow);

        if ($paymentFlow === 'later') {
            return [
                'label' => $label,
                'value' => $this->formatMoney(0),
                'is_currency' => true,
            ];
        }

        return [
            'label' => $label,
            'value' => $this->formatMoney($value ?? 0),
            'is_currency' => true,
        ];
    }

    private function humanizeText(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return Str::of($value)
            ->replace('_', ' ')
            ->replace('-', ' ')
            ->squish()
            ->title()
            ->value();
    }

    private function formatBoolean(?bool $value): ?string
    {
        return $value === null ? null : ($value ? 'Yes' : 'No');
    }

    private function formatPercentage(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.') . '%';
    }

    private function formatMoney(mixed $value): string
    {
        return number_format((float) $value, 2, '.', ',');
    }

    private function formatDate(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return Carbon::parse($value)->format('d M Y');
    }

    private function formatTime(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return Carbon::parse($value)->format('h:i A');
    }

    private function formatDateTime(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return Carbon::parse($value)->format('d M Y, h:i A');
    }
}
