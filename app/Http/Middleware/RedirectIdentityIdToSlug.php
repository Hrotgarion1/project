<?php

namespace App\Http\Middleware;

use App\Models\Identity;
use App\Models\IdentitySlugHistory;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class RedirectIdentityIdToSlug
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ignorar rutas que comienzan con /storage
        if ($request->is('storage/*')) {
            return $next($request);
        }

        $identityId = $request->route('identity_id');
        $slug = $request->route('slug');

        Log::info('RedirectIdentityIdToSlug', [
            'route' => $request->route()->getName(),
            'identity_id' => $identityId,
            'slug' => $slug,
            'uri' => $request->getRequestUri(),
        ]);

        // Si la ruta usa un slug, verificar si es un slug antiguo
        if ($slug) {
            $history = IdentitySlugHistory::where('slug', $slug)->first();
            if ($history) {
                $identity = Identity::findOrFail($history->identity_id);
                if ($identity->slug !== $slug) {
                    Log::info('Redirecting to new slug', ['new_slug' => $identity->slug]);
                    return Redirect::route($request->route()->getName(), ['identity' => $identity->slug]);
                }
            }
            return $next($request);
        }

        // Si la ruta usa identity_id, redirigir al slug actual
        if ($identityId && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $identityId)) {
            $identity = Identity::findOrFail($identityId);
            Log::info('Redirecting from identity_id to slug', ['slug' => $identity->slug]);
            return Redirect::route($request->route()->getName(), ['identity' => $identity->slug]);
        }

        return $next($request);
    }
}
