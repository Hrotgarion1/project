<?php

namespace App\Http\Controllers;

use App\Models\IdentitySuspensionRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use function App\Helpers\mapRole;

class SuspensionRulesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rules = IdentitySuspensionRule::all()->groupBy(['role_type', 'view'])->map(function ($roleGroup) {
            return $roleGroup->map(function ($viewGroup) {
                return $viewGroup->map(function ($rule) {
                    $rule->role_name = mapRole($rule->role_type);
                    return $rule;
                });
            });
        });

        $roles = Role::whereIn('name', array_values(config('roles.types', [])))
            ->pluck('name')
            ->map(function ($role) {
                return [
                    'type' => $role, // "tipo A", "tipo B", etc.
                    'name' => mapRole($role),
                ];
            });

        return response()->json(['rules' => $rules, 'roles' => $roles]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validRoleTypes = array_values(array_unique(config('roles.types', [])));
        $request->validate([
            'role_type' => 'required|in:' . implode(',', $validRoleTypes),
            'is_inviter' => 'required|boolean',
            'view' => 'required|string|max:255',
            'controller' => 'nullable|string|max:255',
            'function' => 'required|string|max:255',
            'allowed' => 'boolean',
        ]);

        $controller = $request->controller;
        if ($controller && !str_starts_with($controller, 'App\\Http\\Controllers\\')) {
            $controller = 'App\\Http\\Controllers\\' . $controller;
        }

        // Verificar si la vista existe
        $routeNames = array_map(fn($route) => $route->getName(), Route::getRoutes()->getRoutes());
        if (!in_array($request->view, $routeNames) && !Route::has($request->view)) {
            return response()->json(['message' => 'La vista especificada no existe en las rutas del sistema.'], 422);
        }

        // Verificar si el controlador y la función existen (si se especifica un controlador)
        if ($controller) {
            if (!class_exists($controller)) {
                return response()->json(['message' => 'El controlador especificado no existe.'], 422);
            }
            if (!method_exists($controller, $request->function)) {
                return response()->json(['message' => 'La función especificada no existe en el controlador.'], 422);
            }
        }

        // Verificar duplicados
        $existingRule = IdentitySuspensionRule::where([
            'role_type' => $request->role_type,
            'is_inviter' => $request->is_inviter,
            'view' => $request->view,
            'function' => $request->function,
        ])->where(function ($query) use ($controller) {
            $query->where('controller', $controller)
                ->orWhereNull('controller');
        })->exists();

        if ($existingRule) {
            return response()->json(['message' => 'Ya existe una regla con esta combinación de rol, tipo, vista y función.'], 422);
        }

        $rule = IdentitySuspensionRule::create([
            'role_type' => $request->role_type,
            'is_inviter' => $request->is_inviter,
            'view' => $request->view,
            'controller' => $controller,
            'function' => $request->function,
            'allowed' => $request->allowed ?? false,
        ]);

        //Log::info('Nueva regla de suspensión registrada', ['rule' => $rule->toArray()]);
        return response()->json(['message' => 'Regla registrada', 'rule' => $rule]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IdentitySuspensionRule $rule)
    {
        $validRoleTypes = array_values(array_unique(config('roles.types', [])));
        $request->validate([
            'role_type' => 'sometimes|in:' . implode(',', $validRoleTypes),
            'is_inviter' => 'sometimes|boolean',
            'view' => 'sometimes|string|max:255',
            'controller' => 'nullable|string|max:255',
            'function' => 'sometimes|string|max:255',
            'allowed' => 'sometimes|boolean',
        ]);

        $controller = $request->controller ?? $rule->controller;
        if ($controller && !str_starts_with($controller, 'App\\Http\\Controllers\\')) {
            $controller = 'App\\Http\\Controllers\\' . $controller;
        }

        // Verificar si la vista existe (si se proporciona)
        if ($request->has('view')) {
            $routeNames = array_map(fn($route) => $route->getName(), Route::getRoutes()->getRoutes());
            if (!in_array($request->view, $routeNames) && !Route::has($request->view)) {
                return response()->json(['message' => 'La vista especificada no existe en las rutas del sistema.'], 422);
            }
        }

        // Verificar si el controlador y la función existen (si se proporciona un controlador)
        if ($request->has('controller') && $controller) {
            if (!class_exists($controller)) {
                return response()->json(['message' => 'El controlador especificado no existe.'], 422);
            }
            if (!method_exists($controller, $request->function ?? $rule->function)) {
                return response()->json(['message' => 'La función especificada no existe en el controlador.'], 422);
            }
        }

        // Verificar duplicados
        $existingRule = IdentitySuspensionRule::where([
            'role_type' => $request->role_type ?? $rule->role_type,
            'is_inviter' => $request->has('is_inviter') ? $request->is_inviter : $rule->is_inviter,
            'view' => $request->view ?? $rule->view,
            'function' => $request->function ?? $rule->function,
        ])->where(function ($query) use ($controller) {
            $query->where('controller', $controller)
                ->orWhereNull('controller');
        })->where('id', '!=', $rule->id)->exists();

        if ($existingRule) {
            return response()->json(['message' => 'Ya existe una regla con esta combinación de rol, tipo, vista y función.'], 422);
        }

        $rule->update([
            'role_type' => $request->role_type ?? $rule->role_type,
            'is_inviter' => $request->has('is_inviter') ? $request->is_inviter : $rule->is_inviter,
            'view' => $request->view ?? $rule->view,
            'controller' => $controller,
            'function' => $request->function ?? $rule->function,
            'allowed' => $request->has('allowed') ? $request->allowed : $rule->allowed,
        ]);

        //Log::info('Regla de suspensión actualizada', ['rule' => $rule->toArray()]);
        return response()->json(['message' => 'Regla actualizada', 'rule' => $rule]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IdentitySuspensionRule $rule)
    {
        $rule->delete();
        //Log::info('Regla de suspensión eliminada', ['rule_id' => $rule->id]);
        return response()->json(['message' => 'Regla eliminada']);
    }
}
