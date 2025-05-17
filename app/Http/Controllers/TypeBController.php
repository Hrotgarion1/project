<?php

namespace App\Http\Controllers;

use App\Models\Identity;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TypeBController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        // Sin dependencias
        $this->middleware('auth');
    }

    public function dashboard($slug)
    {
        $identity = Identity::where('slug', $slug)->firstOrFail();
        $identity_id = $identity->id;
        $this->authorize('view', $identity);
        $identity->load([
            'user:id,name,email',
            'type:id,type',
            'identityDocuments' => fn($query) => $query->where('active', true)->select('id', 'identity_id', 'name', 'type', 'path', 'active'),
        ]);

        $media = Media::where('mediable_type', 'App\\Models\\TypeB')
            ->where('mediable_id', $identity_id)
            ->orderBy('position')
            ->get();

        $uploadUrl = route('media.store', ['mediableType' => 'type-b', 'slug' => $slug]);
        $deleteUrlBase = rtrim(url("/media/type-b/{$slug}"), '/') . '/';
        $reorderUrl = route('media.reorder', ['mediableType' => 'type-b', 'slug' => $slug]);

        Log::info('TypeBController::dashboard URLs', [
            'uploadUrl' => $uploadUrl,
            'deleteUrlBase' => $deleteUrlBase,
            'deleteUrlExample' => $deleteUrlBase . '123',
            'reorderUrl' => $reorderUrl,
            'slug' => $slug,
            'identity_id' => $identity_id,
        ]);

        return Inertia::render('TypeB/Dashboard', [
            'identity' => $identity,
            'identityId' => $identity_id,
            'mediableType' => 'App\\Models\\TypeB',
            'initialMedia' => $media,
            'folders' => ['General', 'Planos', 'Videos'],
            'uploadUrl' => $uploadUrl,
            'deleteUrlBase' => $deleteUrlBase,
            'reorderUrl' => $reorderUrl,
        ]);
    }
}
