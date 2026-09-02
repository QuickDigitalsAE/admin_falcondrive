<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class CustomerDocument extends Model
{

    use SoftDeletes;


    protected $fillable = [

        'customer_id',
        'customer_details',
        'document_no',
        'issue_date',
        'expiry_date',
        'issued_by',
        'identity_name',
        'identity_document_id',
        'description',
        'data',
        'document',
        'path',
        'file_name',
        'file_name_without_extension',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'

    ];

    protected $casts = [
        'customer_details'=>'array'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
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
