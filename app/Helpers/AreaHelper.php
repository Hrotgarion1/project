<?php

namespace App\Helpers;

if (!function_exists('mapArea')) {
    /**
     * Mapea un código de área a su nombre traducido.
     *
     * @param string $code El código de área (ej. 'A')
     * @return string El nombre traducido (ej. 'Educación' en español) o 'Unknown' si no está mapeado
     */
    function mapArea($code)
    {
        $name = config('areas.types.' . strtoupper($code), 'Unknown');
        return __($name);
    }
}

if (!function_exists('mapAreaToCode')) {
    /**
     * Mapea un nombre de área a su código.
     *
     * @param string $areaName El nombre del área (ej. 'Educación' en español)
     * @return string|null El código mapeado (ej. 'A') o null si no está mapeado
     */
    function mapAreaToCode($areaName)
    {
        $areas = array_map(fn($name) => __($name), config('areas.types', []));
        $areas = array_flip($areas);
        return $areas[$areaName] ?? null;
    }
}