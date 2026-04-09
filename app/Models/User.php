<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, HasRoles, Notifiable, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'profile_image',
        'email_verified_at',
        'password',
        'phone',
        'address',
        'dob',
        'cnic',
        'passport',
        'status',
        'emp_id',
        'father_name',
        'mother_name',
        'wife_name',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'profile_image_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        if (!$this->profile_image) {
            return null;
        }

        if (filter_var($this->profile_image, FILTER_VALIDATE_URL)) {
            return $this->profile_image;
        }

        return asset('storage/' . ltrim($this->profile_image, '/'));
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