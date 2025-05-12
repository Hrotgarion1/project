<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeB extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $table = null; // No hay tabla asociada

    protected $fillable = [
        'id',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('position');
    }
}