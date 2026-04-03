<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Request extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'requests';

    protected $fillable = [
        'user_id',
        'manager_id',
        'department_id',
        'title',
        'desc',
        'body',
        'status',
        'category',
        'type',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'manager_id' => 'integer',
        'department_id' => 'integer'
    ];

    // Relationships
    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'table_primary_key', 'id')
                    ->where('table_name', 'requests');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
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
