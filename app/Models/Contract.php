<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Contract extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'contract';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
     protected $fillable = [
        'user_id',
        'title',
        'body',
        'allowance_hra',
        'allowance_transport',
        'allowance_attendance',
        'allowance_medical',
        'start_date',
        'end_date',
        'type',
        'basic_salary',
        'carry_forward',
        'annual_leave',
        'sick_leave',
        'parental_leave',
        'compensatory_leave',
        'medial_insurance',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'user_id' => 'int',

        'allowance_hra' => 'float',
        'allowance_transport' => 'float',
        'allowance_attendance' => 'float',
        'allowance_medical' => 'float',
        'basic_salary' => 'float',

        'carry_forward' => 'integer',
        'annual_leave' => 'integer',
        'sick_leave' => 'integer',
        'parental_leave' => 'integer',
        'compensatory_leave' => 'integer'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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
