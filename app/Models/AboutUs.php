<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AboutUs extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'about_us';

    protected $fillable = [
        'first_section_en',
        'first_section_ar',
        'mission_en',
        'mission_ar',
        'vision_en',
        'vision_ar',
        'seo_title_en',
        'seo_title_ar',
        'seo_brief_en',
        'seo_brief_ar',
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
