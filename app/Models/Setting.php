<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    use SoftDeletes;

    protected $table = 'settings';

    protected $fillable = [
        'key',
        'display_name',
        'value',
        'details',
        'type',
        'order',
        'group',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    protected $appends = [
        'value_url',
    ];

    public function getNormalizedValuePathAttribute(): ?string
    {
        if (!$this->value || !in_array($this->type, ['image', 'file'], true)) {
            return null;
        }

        if (filter_var($this->value, FILTER_VALIDATE_URL)) {
            return $this->value;
        }

        return ltrim(str_replace('\\', '/', $this->value), '/');
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

    public function getDecodedDetailsAttribute()
    {
        if (!$this->details) {
            return null;
        }

        $decoded = json_decode($this->details, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }

    public function getValueUrlAttribute(): ?string
    {
        if (!$this->value) {
            return null;
        }

        if (!in_array($this->type, ['image', 'file'], true)) {
            return null;
        }

        if (filter_var($this->value, FILTER_VALIDATE_URL)) {
            return $this->value;
        }

        $path = $this->normalized_value_path;

        return $path ?  asset(Storage::url(($path))) : null;
    }
}
