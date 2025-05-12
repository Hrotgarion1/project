<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Utilidades extends Model
{
    use HasFactory;

    protected $fillable = ['init_date', 'end_date', 'schedule'];

    /**
     * Calcula el número de días laborables entre dos fechas.
     *
     * @return int
     */
    public function calcularDiasLaborables()
    {
        if (!$this->init_date || !$this->end_date) {
            throw new \Exception("Las fechas de inicio y finalización deben estar definidas.");
        }

        $initDate = Carbon::parse($this->init_date)->startOfDay();
        $endDate = Carbon::parse($this->end_date)->startOfDay();
    
        $workingDays = 0;
        for ($date = $initDate; $date->lte($endDate); $date->addDay()) {
            if ($date->isWeekday()) {
                $workingDays++;
            }
        }
    
        return $workingDays;
    }

    /**
     * Calcula el valor total en función del horario (part-time o full-time) y los días laborables.
     *
     * @return int
     */
    public function calcularValor()
    {
        $diasLaborables = $this->calcularDiasLaborables();
    
        if (!in_array($this->schedule, ['part-time', 'full-time'])) {
            throw new \Exception("El horario debe ser 'part-time' o 'full-time'.");
        }

        $valorPorDia = ($this->schedule == 'part-time') ? 4 : 8;
        $valorTotal = $valorPorDia * $diasLaborables;
    
        $valorTotalRedondeado = round($valorTotal);
    
        return $valorTotalRedondeado;
    }
}
