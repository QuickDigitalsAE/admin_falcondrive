<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSocketConnection extends Model
{
    protected $fillable = [
        'user_id',
        'connection_id',
        'connected_at',
        'disconnected_at',
    ];
}
