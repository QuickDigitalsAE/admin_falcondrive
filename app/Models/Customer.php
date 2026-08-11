<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Customer extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'customers';

    protected $fillable = [
        'customer_id',
        'username',
        'first_name',
        'last_name',
        'mobile_no',
        'email',
        'email_verified_at',
        'gender',
        'nationality',
        'date_of_birth',
        'location_id',
        'street',
        'city',
        'state',
        'country',
        'postal_code',
        'permissions',
        'password',
        'otp',
        'otp_expires_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $hidden = [
        'password',
        'otp',
        'otp_expires_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'customer_id'    => 'integer',
        'location_id'    => 'integer',
        'gender'         => 'integer',
        'created_by'     => 'integer',
        'updated_by'     => 'integer',
        'deleted_by'     => 'integer',
        'deleted_at'     => 'datetime',
    ];

    public function routeNotificationForMail($notification = null): ?string
    {
        return $this->email;
    }
}
