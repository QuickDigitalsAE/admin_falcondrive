<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class Highlight extends Model
{
    use SoftDeletes;

    protected $table = 'highlights';

    protected $fillable = [
        'title_en',
        'title_ar',
        'image',
        'sorting',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'sorting' => 'integer',
    ];

    protected $appends = ['image_url'];

    public function scopeOrderedForListing(Builder $query): Builder
    {
        return $query
            ->orderByRaw("CASE WHEN highlights.sorting IS NULL THEN 1 ELSE 0 END ASC")
            ->orderBy('highlights.sorting')
            ->orderByRaw('LOWER(COALESCE(highlights.title_en, "")) ASC')
            ->orderByDesc('highlights.id');
    }

    public static function nextSorting(?int $ignoreId = null): int
    {
        $maxSorting = static::query()
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->selectRaw("MAX(CAST(COALESCE(NULLIF(sorting, ''), '0') AS UNSIGNED)) as max_sorting")
            ->value('max_sorting');

        return $maxSorting === null ? 0 : ((int) $maxSorting + 1);
    }

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
}
