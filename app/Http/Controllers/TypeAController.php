<?php

namespace App\Http\Controllers;

use App\Models\Identity;
use App\Models\Media;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TypeAController extends Controller
{
    use AuthorizesRequests;
    
    public function __construct()
    {
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

        $media = Media::where('mediable_type', 'App\\Models\\TypeA')
            ->where('mediable_id', $identity_id)
            ->orderBy('position')
            ->get();

        $uploadUrl = route('media.store', ['mediableType' => 'type-a', 'slug' => $slug]);
        $deleteUrlBase = rtrim(url("/media/type-a/{$slug}"), '/') . '/';
        $reorderUrl = route('media.reorder', ['mediableType' => 'type-a', 'slug' => $slug]);

        Log::info('TypeAController::dashboard URLs', [
            'uploadUrl' => $uploadUrl,
            'deleteUrlBase' => $deleteUrlBase,
            'deleteUrlExample' => $deleteUrlBase . '123',
            'reorderUrl' => $reorderUrl,
            'slug' => $slug,
            'identity_id' => $identity_id,
        ]);

        return Inertia::render('TypeA/Dashboard', [
            'identity' => $identity,
            'identityId' => $identity_id,
            'mediableType' => 'App\\Models\\TypeA',
            'initialMedia' => $media,
            'folders' => ['General', 'Planos', 'Videos'],
            'uploadUrl' => $uploadUrl,
            'deleteUrlBase' => $deleteUrlBase,
            'reorderUrl' => $reorderUrl,
        ]);
    }
}
