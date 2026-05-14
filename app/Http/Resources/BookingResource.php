<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class BookingResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'number' => $this->number,
            'email' => $this->email,
            'start_date' => optional($this->start_date)?->format('Y-m-d'),
            'end_date' => optional($this->end_date)?->format('Y-m-d'),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'rental_type' => $this->rental_type,
            'resident_tourist' => $this->resident_tourist,
            'full_insurance' => (bool) $this->full_insurance,
            'additional_driver' => (bool) $this->additional_driver,
            'baby_seat' => (bool) $this->baby_seat,
            'deposit_waiver' => $this->deposit_waiver,
            'delivery_address' => $this->delivery_address,
            'delivery_area' => $this->delivery_area,
            'pickup_address' => $this->pickup_address,
            'pickup_area' => $this->pickup_area,
            'delivery_price' => (string) $this->delivery_price,
            'pickup_price' => (string) $this->pickup_price,
            'coupon_code' => $this->coupon_code,
            'discount_percentage' => (string) $this->discount_percentage,
            'payment_flow' => $this->payment_flow,
            'paid_id' => $this->paid_id,
            'paid_date' => optional($this->paid_date)->toISOString(),
            'paid_status' => $this->paid_status,
            'paid_via' => $this->paid_via,
            'contact_preference' => $this->contact_preference,
            'term_22_years' => (bool) $this->term_22_years,
            'term_6_month_experience' => (bool) $this->term_6_month_experience,
            'description' => $this->description,
            'notes' => $this->notes,
            'request_body' => $this->request_body,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
