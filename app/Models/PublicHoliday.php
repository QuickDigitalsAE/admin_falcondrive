<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class PublicHoliday extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;
    protected $table = 'public_holidays';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'date',
        'compensatory_off_allowed',
        'type',
        'category',
        'year',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'year' => 'integer'
    ];

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'table_primary_key', 'id')
                    ->where('table_name', 'public_holidays');
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
