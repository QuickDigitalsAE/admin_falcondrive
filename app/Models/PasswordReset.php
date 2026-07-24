<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PasswordReset extends Model
{
    protected $fillable = ['email', 'otp', 'expires_at'];
    
    public $timestamps = true;

    protected $dates = ['expires_at'];
    
    public function isExpired()
    {
        return Carbon::now()->greaterThan($this->expires_at);
    }
}
