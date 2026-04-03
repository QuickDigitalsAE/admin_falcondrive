<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Complaint extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;
    protected $table = 'complaints';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'status',
        'assigned_to',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'assigned_to' => 'integer',
    ];

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'table_primary_key', 'id')
                    ->where('table_name', 'complaints');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedToUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
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
