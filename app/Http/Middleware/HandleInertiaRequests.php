<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Establecer el idioma desde la sesión o usar el predeterminado
        $locale = session('locale', config('app.locale'));
        app()->setLocale($locale);

        // Lista de idiomas soportados
        $supportedLocales = ['ca', 'de', 'en', 'es', 'eu', 'fr', 'gl', 'it', 'pt', 'ru'];

        return [
            ...parent::share($request),
            'locale' => fn () => $locale,
            'supportedLocales' => fn () => $supportedLocales,
            'validation' => fn () => trans('validation'),
            'user' => fn () => $request->user() ? [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'roles' => $request->user()->roles->pluck('name'),
                'permissions' => $request->user()->getAllPermissions()->pluck('name'),
            ] : null,
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
            ],
            'errors' => fn () => $request->session()->get('errors') ? $request->session()->get('errors')->all() : (object) [],
        ];
    }
}
