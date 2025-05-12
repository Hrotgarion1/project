<?php

namespace App\Http\Controllers;

use App\Models\Identity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TypeCController extends Controller
{
    public function dashboard($slug)
    {
        // Intentar cargar la identidad por slug
        $identity = Identity::where('slug', $slug)->firstOrFail();

        // Verificar autorización
        $this->authorize('view', $identity);

        // Cargar relaciones necesarias
        $identity->load([
            'user:id,name,email',
            'type:id,type',
            'identityDocuments' => fn($query) => $query->where('active', true)->select('id', 'identity_id', 'name', 'type', 'path', 'active'),
        ]);

        return Inertia::render('TypeC/Dashboard', [
            'identity' => $identity,
        ]);
    }
}
