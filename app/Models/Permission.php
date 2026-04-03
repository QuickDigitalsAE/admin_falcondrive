<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Permission extends SpatiePermission
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'guard_name',
        'table_name',
        'deleted_by'
    ];

}
