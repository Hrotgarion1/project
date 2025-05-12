<?php

namespace App\Http\Controllers;

use App\Models\Identity;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TypeBController extends Controller
{
    public function __construct()
    {
        // Sin dependencias
        $this->middleware('auth');
    }

    public function dashboard($slug)
    {
        // Intentar cargar la identidad por slug
        $identity = Identity::where('slug', $slug)->firstOrFail();
        $identity_id = $identity->id;

        // Verificar autorización
        $this->authorize('view', $identity);

        // Cargar relaciones necesarias
        $identity->load([
            'user:id,name,email',
            'type:id,type',
            'identityDocuments' => fn($query) => $query->where('active', true)->select('id', 'identity_id', 'name', 'type', 'path', 'active'),
        ]);

        // Cargar medios desde la tabla media
        $media = Media::where('mediable_type', 'App\\Models\\TypeB')
            ->where('mediable_id', $identity_id)
            ->orderBy('position')
            ->get();

        // Generar URLs para MediaUploader
        $uploadUrl = route('media.store', ['mediableType' => 'type-b', 'slug' => $slug]);
        $deleteUrl = fn ($mediaId) => route('media.destroy', ['mediableType' => 'type-b', 'slug' => $slug, 'mediaId' => $mediaId]);
        $reorderUrl = route('media.reorder', ['mediableType' => 'type-b', 'slug' => $slug]);

        Log::info('TypeBController::dashboard URLs', [
            'uploadUrl' => $uploadUrl,
            'deleteUrl' => $deleteUrl(123),
            'reorderUrl' => $reorderUrl,
            'slug' => $slug,
            'identity_id' => $identity_id,
        ]);

        return Inertia::render('TypeB/Dashboard', [
            'identity' => $identity,
            'identityId' => $identity_id,
            'identitySlug' => $slug,
            'mediableType' => 'App\\Models\\TypeB',
            'initialMedia' => $media,
            'uploadUrl' => $uploadUrl,
            'deleteUrl' => $deleteUrl,
            'reorderUrl' => $reorderUrl,
            'folders' => ['General', 'Planos', 'Videos'],
        ]);
    }
}
