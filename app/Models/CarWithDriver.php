<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class CarWithDriver extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'car_with_drivers';

    protected $fillable = [
        'slug',
        'display_en',
        'display_ar',
        'meta_title_en',
        'meta_description_en',
        'meta_title_ar',
        'meta_description_ar',
        'card_image',
        'card_header_en',
        'card_text_en',
        'card_header_ar',
        'card_text_ar',
        'header_en',
        'header_ar',
        'cars',
        'content_en',
        'content_ar',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $appends = [
        'card_image_url',
    ];

    public function getCardImageUrlAttribute(): ?string
    {
        if (!$this->card_image) {
            return null;
        }

        if (filter_var($this->card_image, FILTER_VALIDATE_URL)) {
            return $this->card_image;
        }

        return asset(Storage::url($this->card_image));
    }

    public function carsRelation()
    {
        return $this->belongsToMany(Car::class, 'car_car_with_driver')
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedByUser()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
