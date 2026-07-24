<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlacklistedToken extends Model
{
    protected $fillable = ['token'];
}
