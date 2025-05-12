<?php

namespace App\Http\Controllers;

use App\Models\Identity;
use App\Models\IdentityMeta;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class IdentityController extends Controller
{

    public function getRoles()
        {
            return response()->json(Role::pluck('name')); // Devuelve solo los nombres de los roles
        }

    /**
     * Asignar una identidad aprobada a un usuario (admin/editor)
     */
    public function store(Request $request, User $user) {
        $request->validate([
            'type' => 'required|string',
            'email' => 'required|email|unique:identities,email',
            'name' => 'required|string',
            'address' => 'nullable|string',
            'meta' => 'nullable|array',
        ]);

        if (!auth()->user()->hasRole(['admin', 'editor'])) {
            abort(403, 'No tienes permiso para asignar una identidad.');
        }

        $identity = $user->identities()->create([
            'type' => $request->type,
            'email' => $request->email,
            'name' => $request->name,
            'address' => $request->address,
            'status' => 'approved',
        ]);

        // Guardar datos personalizados en IdentityMeta
        if ($request->has('meta')) {
            foreach ($request->meta as $key => $value) {
                IdentityMeta::create([
                    'identity_id' => $identity->id,
                    'key' => $key,
                    'value' => is_array($value) ? json_encode($value) : $value,
                    'type' => gettype($value),
                ]);
            }
        }

        return response()->json(['message' => 'Identidad asignada correctamente', 'identity' => $identity]);
    }

    /**
     * Actualizar datos personalizados de una identidad
     */
    public function updateMeta(Request $request, Identity $identity) {
        if (auth()->id() !== $identity->user_id && !auth()->user()->hasRole(['admin', 'editor'])) {
            abort(403, 'No tienes permiso para modificar esta identidad.');
        }

        $request->validate([
            'meta' => 'required|array',
        ]);

        foreach ($request->meta as $key => $value) {
            IdentityMeta::updateOrCreate(
                ['identity_id' => $identity->id, 'key' => $key],
                ['value' => is_array($value) ? json_encode($value) : $value, 'type' => gettype($value)]
            );
        }

        return response()->json(['message' => 'Datos actualizados']);
    }

    
}
