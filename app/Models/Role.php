<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as SpatieRole;
use App\Traits\LogsActivity;

class Role extends SpatieRole
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'guard_name',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

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
