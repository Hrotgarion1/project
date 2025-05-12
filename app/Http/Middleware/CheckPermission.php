<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, $permissionOrRole)
    {
        $user = Auth::user();

        info('User ID: ' . $user->id);
        info('Permission or Role: ' . $permissionOrRole);

        if ($user->can($permissionOrRole) || $user->hasRole($permissionOrRole)) {
            return $next($request);
        }

        abort(403, 'No tienes permisos para acceder a esta página, si los necesitas, habla con el administrador');
    }
}
