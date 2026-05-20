<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class PromoCode extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'title',
        'discount_type',
        'discount_value',
        'minimum_amount',
        'usage_limit',
        'start_date',
        'expiry_date',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'used_count' => 'integer',
        'status' => 'integer',
        'start_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function isValidForAmount(float $amount): array
    {
        $today = Carbon::today();

        if ((int) $this->status !== 1) {
            return [false, 'This promo code is inactive.'];
        }

        if ($this->start_date && $today->lt($this->start_date)) {
            return [false, 'This promo code is not active yet.'];
        }

        if ($this->expiry_date && $today->gt($this->expiry_date)) {
            return [false, 'This promo code has expired.'];
        }


        if ($amount < (float) $this->minimum_amount) {
            return [false, 'Minimum amount required is AED ' . number_format((float) $this->minimum_amount, 2) . '.'];
        }

        return [true, null];
    }

    public function calculateDiscount(float $amount): float
    {
        if ($this->discount_type === 'percentage') {
            return round(($amount * (float) $this->discount_value) / 100, 2);
        }

        return round((float) $this->discount_value, 2);
    }
}
