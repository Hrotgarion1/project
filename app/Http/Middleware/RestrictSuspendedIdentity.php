<?php

namespace App\Http\Middleware;

use App\Models\Identity;
use Closure;
use Illuminate\Http\Request;
use App\Models\IdentitySuspensionRule;
use App\Models\IdentitySuspension;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RestrictSuspendedIdentity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (!$user) {
            return $next($request);
        }

        $route = $request->route();
        if (!$route) {
            return $next($request);
        }

        $routeName = $route->getName();
        $controller = $route->getControllerClass();
        $action = $route->getActionMethod();
        $identityId = $request->route('identity_id');
        $slug = $request->route('identity');

        // Obtener identity_id desde slug o identity_id
        $identity = null;
        if ($slug) {
            $identity = Identity::where('slug', $slug)->first();
        } elseif ($identityId) {
            $identity = Identity::find($identityId);
        }

        if (!$identity) {
            return $next($request);
        }

        // Buscar suspensiones activas para este usuario y la identidad específica
        $suspension = IdentitySuspension::where('user_id', $user->id)
            ->where('identity_id', $identity->id)
            ->first();

        if (!$suspension) {
            return $next($request);
        }

        // Verificar si la identidad está suspendida
        if ($identity->status !== 'suspended') {
            return $next($request);
        }

        // Buscar reglas aplicables para este role_type y la identidad suspendida
        $rules = IdentitySuspensionRule::where(function ($query) use ($suspension) {
                $query->where('identity_id', $suspension->identity_id)
                      ->orWhereNull('identity_id');
            })
            ->where('role_type', $suspension->role_type)
            ->where(function ($query) use ($routeName, $controller, $action) {
                $query->where('view', $routeName)
                      ->orWhere(function ($query) use ($controller, $action) {
                          $query->where('controller', $controller)
                                ->where('function', $action);
                      });
            })
            ->get();

        foreach ($rules as $rule) {
            if (!$rule->allowed) {
                return response()->view('errors.suspended', [
                    'message' => 'Acceso denegado debido a una suspensión activa de la identidad ' . $suspension->identity_id,
                    'rule' => $rule,
                    'identity_id' => $suspension->identity_id,
                ], 403);
            }
        }

        return $next($request);
    }
}
