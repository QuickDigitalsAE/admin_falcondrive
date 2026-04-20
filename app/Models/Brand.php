<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
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
        'logo',
        'sorting',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'sorting' => 'integer',
    ];

    protected $appends = [
        'logo_url',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        if (filter_var($this->logo, FILTER_VALIDATE_URL)) {
            return $this->logo;
        }

        return asset('storage/' . ltrim($this->logo, '/'));
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
        return $this->hasMany(Car::class, 'brand_id')->orderedForListing();
    }

    public function scopeOrderedForListing(Builder $query): Builder
    {
        return $query
            ->orderByRaw("CASE WHEN brands.sorting IS NULL THEN 1 ELSE 0 END ASC")
            ->orderBy('brands.sorting')
            ->orderByRaw('LOWER(COALESCE(brands.name_en, "")) ASC')
            ->orderByDesc('brands.id');
    }

    public static function nextSorting(?int $ignoreId = null): int
    {
        $maxSorting = static::query()
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->selectRaw("MAX(CAST(COALESCE(NULLIF(sorting, ''), '0') AS UNSIGNED)) as max_sorting")
            ->value('max_sorting');

        return $maxSorting === null ? 0 : ((int) $maxSorting + 1);
    }
}
