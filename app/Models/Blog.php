<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class Blog extends Model
{
    use SoftDeletes;

    protected $table = 'blogs';

    protected $fillable = [
        'title_en',
        'title_ar',
        'blog_description_en',
        'blog_description_ar',
        'slug',
        'seo_title_en',
        'seo_title_ar',
        'seo_brief_en',
        'seo_brief_ar',
        'image',
        'blog_schedule',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'blog_schedule' => 'datetime',
    ];

    protected $appends = ['image_url'];

    public function scopePubliclyAvailable(Builder $query): Builder
    {
        $now = now(config('app.timezone'));

        return $query->where(function (Builder $innerQuery) use ($now) {
            $innerQuery->where(function (Builder $scheduleQuery) use ($now) {
                $scheduleQuery->whereNotNull('blog_schedule')
                    ->where('blog_schedule', '<=', $now);
            })->orWhere(function (Builder $createdQuery) use ($now) {
                $createdQuery->whereNull('blog_schedule')
                    ->where('created_at', '<=', $now);
            });
        });
    }

    public function publishedAt(): ?Carbon
    {
        return $this->blog_schedule ?: $this->created_at;
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
