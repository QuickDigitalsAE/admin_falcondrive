<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\LogsActivity;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'leave_requests';

    protected $fillable = [
        'user_id',
        'leave_type',
        'start_date',
        'end_date',
        'no_of_days',
        'balance_annual',
        'balance_sick',
        'balance_parental',
        'balance_comp',
        'supporting_document',
        'reason',
        'approver_user_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'user_id'  => 'integer',
        'approver_user_id'  => 'integer',
        'no_of_days' => 'integer',
        'supporting_document' => 'integer',
        'balance_annual' => 'double',
        'balance_sick' => 'double',
        'balance_parental' => 'double',
        'balance_comp' => 'double',
    ];

    // Relationships

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'table_primary_key', 'id')
                    ->where('table_name', 'leave_requests');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approverUser()
    {
        return $this->belongsTo(User::class, 'approver_user_id');
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