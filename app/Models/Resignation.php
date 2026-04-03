<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Resignation extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'resignations';

    protected $fillable = [
        'user_id',
        'resignation_date',
        'last_working_day',
        'reason',
        'type',
        'notice_period',
        'asset_return_checklist',
        'leave_balance',
        'air_ticket_entitlement',
        'final_settlement_amount',
        'it_approver_user_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'notice_period' => 'integer',
        'asset_return_checklist' => 'integer',
        'leave_balance' => 'float',
        'air_ticket_entitlement' => 'integer',
        'final_settlement_amount' => 'float',
        'it_approver_user_id' => 'integer',
    ];

    // Relationships
    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'table_primary_key', 'id')
                    ->where('table_name', 'resignations');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function itApproverUser()
    {
        return $this->belongsTo(User::class, 'it_approver_user_id');
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
