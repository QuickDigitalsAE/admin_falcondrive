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
            'rental_price' => (string) $this->rental_price,
            'rental_duration' => $this->rental_duration,
            'resident_tourist' => $this->resident_tourist,
            'full_insurance' => (bool) $this->full_insurance,
            'full_insurance_price' => (string) $this->full_insurance_price,
            'additional_driver' => (bool) $this->additional_driver,
            'additional_driver_charges' => (string) $this->additional_driver_charges,
            'baby_seat' => (bool) $this->baby_seat,
            'baby_seat_price' => (string) $this->baby_seat_price,
            'deposit_waiver' => $this->deposit_waiver,
            'deposit_waiver_price' => (string) $this->deposit_waiver_price,
            'delivery_location' => $this->delivery_location,
            'delivery_custom_address' => $this->delivery_custom_address,
            'delivery_location_price' => (string) $this->delivery_location_price,
            'different_city_dropoff_fee' => (string) $this->different_city_dropoff_fee,
            'self_pickup_location_id' => $this->self_pickup_location_id,
            'self_pickup_location' => $this->self_pickup_location,
            'self_pickup_address' => $this->self_pickup_address,
            'return_location' => $this->return_location,
            'return_custom_address' => $this->return_custom_address,
            'return_location_price' => (string) $this->return_location_price,
            'self_return_location_id' => $this->self_return_location_id,
            'self_return_location' => $this->self_return_location,
            'self_return_address' => $this->self_return_address,
            'coupon_code' => $this->coupon_code,
            'coupon_amount' => (string) $this->coupon_amount,
            'pay_now_discount' => (string) $this->pay_now_discount,
            'discount_percentage' => (string) $this->discount_percentage,
            'subtotal' => (string) $this->subtotal,
            'vat_percentage' => (string) $this->vat_percentage,
            'vat_amount' => (string) $this->vat_amount,
            'total_amount' => (string) $this->total_amount,
            'payment_flow' => $this->payment_flow,
            'pay_now_20%_to_Reserve' => (string) $this->{'pay_now_20%_to_Reserve'},
            'pay_at_pickup_80%' => (string) $this->{'pay_at_pickup_80%'},
            'paid_id' => $this->paid_id,
            'paid_date' => optional($this->paid_date)->toISOString(),
            'paid_status' => $this->paid_status,
            'paid_via' => $this->paid_via,
            'contact_preference' => $this->contact_preference,
            'term_22_years' => (bool) $this->term_22_years,
            'term_6_month_experience' => (bool) $this->term_6_month_experience,
            'send_booking_id' => $this->send_booking_id,
            'notes' => $this->notes,
            'speed_response' => $this->speed_response,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
