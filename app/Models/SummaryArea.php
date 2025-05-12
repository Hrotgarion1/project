<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SummaryArea extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'user_id'];

    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function get_puntuacion_1_attribute()
    {
        return $this->areas()->with('records')->get()->sum(fn($area) => $area->puntuacion_1);
    }

    public function get_puntuacion_2_attribute()
    {
        return $this->areas()->with('records')->get()->sum(fn($area) => $area->puntuacion_2);
    }

    public function get_puntuacion_3_attribute()
    {
        return $this->puntuacion_1 + $this->puntuacion_2;
    }
}
