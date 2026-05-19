<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Car extends Model
{
    use SoftDeletes;

    protected $table = 'cars';

    protected $fillable = [
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'price_daily',
        'price_weekly',
        'price_monthly',
        'full_insurance_amount',
        'additional_driver_amount',
        'baby_seat_amount',
        'deposit_amount',
        'waiver_amount',
        'different_city_dropoff_fee',
        'main_image',
        'images',
        'model',
        'featured',
        'featured_sorting',
        'engine',
        'seats',
        'doors',
        'deposit',
        'luggage',
        'cruise_control',
        'bluetooth',
        'automatic',
        'parking_sensor',
        'navigation',
        'carplay',
        'camera',
        'slug',
        'seo_title_en',
        'seo_title_ar',
        'seo_brief_en',
        'seo_brief_ar',
        'brand_id',
        'stock',
        'cdw_daily',
        'cdw_weekly',
        'cdw_monthly',
        'sorting',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'images' => 'array',
        'brand_id' => 'integer',
        'featured' => 'boolean',
        'featured_sorting' => 'integer',
        'cruise_control' => 'boolean',
        'bluetooth' => 'boolean',
        'automatic' => 'boolean',
        'parking_sensor' => 'boolean',
        'navigation' => 'boolean',
        'carplay' => 'boolean',
        'camera' => 'boolean',
        'stock' => 'boolean',
        'sorting' => 'integer',
    ];

    protected $appends = [
        'main_image_url',
        'gallery_image_urls',
    ];

    public function getMainImageUrlAttribute(): ?string
    {
        return $this->resolveStorageUrl($this->main_image);
    }

    public function getGalleryImageUrlsAttribute(): array
    {
        return collect($this->images ?? [])
            ->map(fn ($path) => $this->resolveStorageUrl($path))
            ->filter()
            ->values()
            ->all();
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function scopeOrderedForListing(Builder $query): Builder
    {
        return $query
            ->orderByRaw('CASE WHEN cars.brand_id IS NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('cars.brand_id')
            ->orderByRaw("CASE WHEN cars.sorting IS NULL OR cars.sorting = '' THEN 1 ELSE 0 END ASC")
            ->orderByRaw("CAST(COALESCE(NULLIF(cars.sorting, ''), '999999') AS UNSIGNED) ASC")
            ->orderByRaw('LOWER(COALESCE(cars.name_en, "")) ASC')
            ->orderByDesc('cars.id');
    }

    public static function nextSortingForBrand(int $brandId, ?int $ignoreId = null): int
    {
        $maxSorting = static::query()
            ->where('brand_id', $brandId)
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->selectRaw("MAX(CAST(COALESCE(NULLIF(sorting, ''), '0') AS UNSIGNED)) as max_sorting")
            ->value('max_sorting');

        return $maxSorting === null ? 0 : ((int) $maxSorting + 1);
    }

    public function scopeOrderedForFeaturedListing(Builder $query): Builder
    {
        return $query
            ->orderByRaw("CASE WHEN cars.featured_sorting IS NULL THEN 1 ELSE 0 END ASC")
            ->orderByRaw("CAST(COALESCE(NULLIF(cars.featured_sorting, ''), '999999') AS UNSIGNED) ASC")
            ->orderByRaw('LOWER(COALESCE(cars.name_en, "")) ASC')
            ->orderByDesc('cars.id');
    }

    public static function nextFeaturedSorting(?int $ignoreId = null): int
    {
        $maxSorting = static::query()
            ->where('featured', 1)
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->selectRaw("MAX(CAST(COALESCE(NULLIF(featured_sorting, ''), '0') AS UNSIGNED)) as max_sorting")
            ->value('max_sorting');

        return $maxSorting === null ? 0 : ((int) $maxSorting + 1);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'car_category')
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }

    public function carWithDrivers()
    {
        return $this->belongsToMany(CarWithDriver::class, 'car_car_with_driver')
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }

    public function driverPages()
    {
        return $this->carWithDrivers();
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'location_cars', 'car_id', 'locations_id')
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

    private function resolveStorageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return asset(Storage::url($path));
    }
}
