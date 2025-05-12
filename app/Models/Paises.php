<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paises extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function categorias()
    {
        return $this->hasMany(Categoria::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function belongings()
    {
        return $this->hasMany(Belonging::class, 'country_id');
    }
}
