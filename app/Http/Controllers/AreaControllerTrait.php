<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

trait AreaControllerTrait
{
    protected function calculateValue(array $data)
    {
        $initDate = Carbon::parse($data['init_date']);
        $endDate = $data['currently'] === 'yes' ? Carbon::today() : Carbon::parse($data['end_date'] ?? Carbon::today());
        
        $festivos = $this->getFestivosNacionales();
        $festivos = array_map(function ($date) {
            return Carbon::parse($date)->startOfDay();
        }, $festivos);

        $days = $initDate->diffInDaysFiltered(function (Carbon $date) use ($festivos) {
            return !$date->isWeekend() && !in_array($date->startOfDay(), $festivos);
        }, $endDate);

        $hoursPerDay = (int) $data['schedule'] + (int) ($data['overtime'] ?? 0);
        return $days * $hoursPerDay;
    }

    protected function getFestivosNacionales()
    {
        return [
            '2025-01-01',
            '2025-05-01',
            '2025-12-25',
        ];
    }
}