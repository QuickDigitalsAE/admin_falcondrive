<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'seo_title_en',
        'seo_title_ar',
        'seo_brief_en',
        'seo_brief_ar',
        'slug',
        'image',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        return asset(Storage::url($this->image));
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

    public function cars()
    {
        return $this->belongsToMany(Car::class, 'car_category')
            ->withTimestamps()
            ->wherePivotNull('deleted_at')
            ->orderedForListing();
    }
}
