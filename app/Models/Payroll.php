<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Payroll extends Model
{
    use SoftDeletes, LogsActivity;
    
    protected $fillable = [
        'user_id',
        'contract_id',
        'basic_salary',
        'allowance_hra',
        'allowance_transport',
        'allowance_attendance',
        'allowance_medical',
        'deduction_late',
        'deduction_loan',
        'leaves_without_pay',
        'net_salary',
        'wps_code',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'contract_id' => 'integer',
        'basic_salary' => 'float',
        'allowance_hra' => 'float',
        'allowance_transport' => 'float',
        'allowance_attendance' => 'float',
        'allowance_medical' => 'float',
        'deduction_late' => 'float',
        'deduction_loan' => 'float',
        'leaves_without_pay' => 'float',
        'net_salary' => 'float'
    ];

    // Relationships
    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'table_primary_key', 'id')
                    ->where('table_name', 'payrolls');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
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
