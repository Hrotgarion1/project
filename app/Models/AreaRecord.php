<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AreaRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'area_records';

    protected $fillable = [
        'user_id',
        'belonging_id',
        'pais_id',
        'recordable_id',
        'recordable_type',
        'value',
        'status',
        'puntuacion_1',
        'puntuacion_2',
        'recordable_name',
    ];

    protected $casts = [
        'puntuacion_1' => 'integer',
        'puntuacion_2' => 'integer',
        'value' => 'integer',
    ];

    public function recordable()
    {
        return $this->morphTo();
    }

    public function belonging()
    {
        return $this->belongsTo(Belonging::class);
    }

    public function pais()
    {
        return $this->belongsTo(Paises::class, 'pais_id');
    }

    public function getAreaKeyAttribute()
    {
        $type = $this->recordable_type;
        return match ($type) {
            'App\Models\AreaA' => 'A',
            'App\Models\AreaB' => 'B',
            'App\Models\AreaC' => 'C',
            'App\Models\AreaD' => 'D',
            'App\Models\AreaE' => 'E',
            'App\Models\AreaF' => 'F',
            'App\Models\AreaG' => 'G',
            'App\Models\AreaH' => 'H',
            default => null,
        };
    }
}
