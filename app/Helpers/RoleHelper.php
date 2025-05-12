<?php

namespace App\Helpers;

if (!function_exists('App\Helpers\mapRole')) {
    /**
     * Mapea un tipo de rol a su nombre legible.
     *
     * @param string $type El tipo de rol (ej. 'tipo A')
     * @return string El nombre mapeado (ej. 'Cebolla') o 'Unknown' si no está mapeado
     */
    function mapRole($type)
    {
        $roles = array_flip(config('roles.types', []));
        return $roles[$type] ?? 'Unknown';
    }
}

if (!function_exists('App\Helpers\mapRoleToType')) {
    /**
     * Mapea un nombre de rol a su tipo.
     *
     * @param string $roleName El nombre del rol (ej. 'cebolla')
     * @return string|null El tipo mapeado (ej. 'tipo A') o null si no está mapeado
     */
    function mapRoleToType($roleName)
    {
        return config('roles.types.' . strtolower($roleName));
    }
}
