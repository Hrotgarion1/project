<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'value', 'pais_id'];

    public function area_a()
    {
        return $this->hasMany(AreaA::class);
    }

    public function pais()
    {
        return $this->belongsTo(Paises::class);
    }

}
