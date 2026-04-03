<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;
use Illuminate\Support\Carbon;

class Shift extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'shift';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'start_time',
        'end_time',
        'break_duration',
        'grace_time',
        'alternate_saturday_off',
        'user_id',
        'type',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'user_id' => 'integer'
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

    public function getStartTimeAttribute($value)
    {
        return Carbon::parse($value)->format('H:i'); // returns 09:00
    }

    public function getEndTimeAttribute($value)
    {
        return Carbon::parse($value)->format('H:i'); // returns 06:00
    }
}
