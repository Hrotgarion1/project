<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'summary_area_id', 'puntuacion_1', 'puntuacion_2', 'puntuacion_3'];

    public function summaryArea()
    {
        return $this->belongsTo(SummaryArea::class);
    }

    public function categorias()
    {
        return $this->hasMany(Categoria::class);
    }

    public function areaRecords()
    {
        $modelMap = [
            'A' => AreaA::class,
            'B' => AreaB::class,
            'C' => AreaC::class,
            'D' => AreaD::class,
            'E' => AreaE::class,
            'F' => AreaF::class,
            'G' => AreaG::class,
            'H' => AreaH::class,
        ];

        $model = $modelMap[$this->name] ?? AreaA::class;
        return $this->hasMany($model);
    }

    public function records()
    {
        $modelMap = [
            'A' => AreaA::class,
            'B' => AreaB::class,
            'C' => AreaC::class,
            'D' => AreaD::class,
            'E' => AreaE::class,
            'F' => AreaF::class,
            'G' => AreaG::class,
            'H' => AreaH::class,
        ];

        return $this->hasManyThrough(
            AreaRecord::class,
            $modelMap[$this->name] ?? AreaA::class,
            'area_id', // Clave foránea en AreaA, AreaB, etc.
            'recordable_id', // Clave foránea en AreaRecord
            'id', // Clave primaria en Area
            'id' // Clave primaria en AreaA, AreaB, etc.
        )->where('recordable_type', $modelMap[$this->name] ?? AreaA::class);
    }

    public function getPuntuacion1Attribute()
    {
        return $this->records()->where('status', '1')->sum('value');
    }

    public function getPuntuacion2Attribute()
    {
        return $this->records()->where('status', '2')->sum('value');
    }

    public function getPuntuacion3Attribute()
    {
        return $this->puntuacion_1 + $this->puntuacion_2;
    }
}
