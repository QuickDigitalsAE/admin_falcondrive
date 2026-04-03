<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\LogsActivity;

class Attendance extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'date',
        'shift_id',
        'clock_in',
        'clock_out',
        'total_worked_hours',
        'is_late',
        'late_minutes',
        'late_reason',
        'is_early_departure',
        'early_departure_minutes',
        'adjustment_request_status',
        'late_deduction_applied',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

   protected $casts = [
        'user_id' => 'integer',
        'shift_id' => 'integer',

        'total_worked_hours' => 'float',

        'late_minutes' => 'integer',
        'early_departure_minutes' => 'integer'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
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

