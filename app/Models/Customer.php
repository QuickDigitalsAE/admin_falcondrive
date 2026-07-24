<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'customers';

    protected $fillable = [
        'customer_id',
        'username',
        'first_name',
        'last_name',
        'mobile_no',
        'email',
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
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'customer_id'    => 'integer',
        'location_id'    => 'integer',
        'gender'         => 'integer',
        'created_by'     => 'integer',
        'updated_by'     => 'integer',
        'deleted_by'     => 'integer',
        'deleted_at'     => 'datetime',
    ];
}