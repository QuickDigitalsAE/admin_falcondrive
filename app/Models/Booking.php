<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'bookings';

    protected $fillable = [
        'name',
        'number',
        'email',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'rental_type',
        'resident_tourist',
        'full_insurance',
        'additional_driver',
        'baby_seat',
        'deposit_waiver',
        'delivery_address',
        'delivery_area',
        'pickup_address',
        'pickup_area',
        'delivery_price',
        'pickup_price',
        'coupon_code',
        'discount_percentage',
        'payment_flow',
        'paid_id',
        'paid_date',
        'paid_status',
        'paid_via',
        'contact_preference',
        'term_22_years',
        'term_6_month_experience',
        'description',
        'notes',
        'request_body',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'string',
        'end_time' => 'string',
        'full_insurance' => 'bool',
        'additional_driver' => 'bool',
        'baby_seat' => 'bool',
        'delivery_price' => 'decimal:2',
        'pickup_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'paid_date' => 'datetime',
        'term_22_years' => 'bool',
        'term_6_month_experience' => 'bool',
    ];
}
