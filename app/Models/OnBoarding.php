<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Onboarding extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'onboarding';

    protected $fillable = [
        'job_id',
        'user_id',
        'is_cv_collected',
        'interview_1_by',
        'interview_1_feedback',
        'interview_1_status',
        'interview_1_datetime',
        'interview_2_by',
        'interview_2_feedback',
        'interview_2_status',
        'interview_2_datetime',
        'interview_3_by',
        'interview_3_feedback',
        'interview_3_status',
        'interview_3_datetime',
        'offer_status',
        'offer_amount',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'job_id' => 'integer',
        'is_cv_collected' => 'integer',
        'interview_1_by' => 'integer',
        'interview_2_by' => 'integer',
        'interview_3_by' => 'integer',
        'offer_amount' => 'float'
    ];

    // Relationships
    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'table_primary_key', 'id')
                    ->where('table_name', 'onboarding');
    }

    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function interview1By()
    {
        return $this->belongsTo(User::class, 'interview_1_by');
    }

    public function interview2By()
    {
        return $this->belongsTo(User::class, 'interview_2_by');
    }

    public function interview3By()
    {
        return $this->belongsTo(User::class, 'interview_3_by');
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
