<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryReturnLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'detail',
        'web_id',
        'pickup_location_id',
        'longitude',
        'latitude',
        'price',
        'type',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

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
