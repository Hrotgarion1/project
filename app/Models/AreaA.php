<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AreaA extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'area_a';

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
        'category_id',
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

    public function category()
    {
        return $this->belongsTo(Categoria::class);
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
            'id', // Clave primaria en Area
            'id', // Clave primaria en Belonging
            'area_id', // Clave foránea en AreaA
            'belonging_id' // Clave foránea en Area
        );
    }
}
