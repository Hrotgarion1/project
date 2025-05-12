<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AreaH extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'area_h';

    protected $fillable = [
        'area_id',
        'user_id',
        'name',
        'description',
        'init_date',
        'end_date',
        'schedule',
        'overtime',
        'currently',
        'value',
        'last_calculated_date',
        'details',
    ];

    protected $casts = [
        'init_date' => 'date',
        'end_date' => 'date',
        'value' => 'integer',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function records()
    {
        return $this->morphMany(AreaRecord::class, 'recordable');
    }

    public function belonging()
    {
        return $this->hasOneThrough(
            Belonging::class,
            Area::class,
            'id',
            'id',
            'area_id',
            'belonging_id'
        );
    }
}
