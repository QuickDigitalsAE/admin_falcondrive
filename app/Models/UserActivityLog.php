<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'model_type', 'table_name', 'changes', 'action'
    ];

    protected $casts = [
        'changes' => 'array',
    ];
}
