<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Identity;
use App\Models\Invitacion;
use function App\Helpers\mapRole;
use function App\Helpers\mapArea;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $locale = session('locale', config('app.locale'));
        app()->setLocale($locale);
        
        // Lista de idiomas soportados
        $supportedLocales = ['ca', 'de', 'en', 'es', 'eu', 'fr', 'gl', 'it', 'pt', 'ru'];

        $user = $request->user();
        $approvedIdentities = $user ? Identity::where('user_id', $user->id)
            ->where('status', 'approved')
            ->select('id', 'name', 'type', 'slug')
            ->get()
            ->groupBy('type')
            ->toArray() : [];

        $invitedIdentities = $user ? Invitacion::where('invitado_id', $user->id)
            ->where('status', 'approved')
            ->with(['identity' => function ($query) {
                $query->select('id', 'name', 'type', 'slug');
            }])
            ->select('id', 'identity_id', 'role')
            ->get()
            ->groupBy('identity_id')
            ->map(function ($invitations) {
                $identity = $invitations->first()->identity;
                return [
                    'id' => $identity->id,
                    'name' => $identity->name,
                    'type' => $identity->type,
                    'slug' => $identity->slug,
                    'roles' => $invitations->pluck('role')->all(),
                    'roleNames' => $invitations->pluck('role')->map(fn($role) => mapRole($role))->all(),
                ];
            })
            ->values()
            ->groupBy('type')
            ->toArray() : [];

        // Contar invitaciones pendientes
        $invitationsCount = $user ? Invitacion::where('invitado_id', $user->id)
            ->where('status', 'pending')
            ->count() : 0;

        // Construir el array de áreas con nombres traducidos y rutas
        $areas = [
            ['code' => 'global', 'name' => __('Global view'), 'route' => 'skyfall.area-global'],
            ['code' => 'resumen', 'name' => __('Area Summary'), 'route' => 'skyfall.area-resumen'],
            ['code' => 'A', 'name' => mapArea('A'), 'route' => 'skyfall.area-a.index'],
            ['code' => 'B', 'name' => mapArea('B'), 'route' => 'skyfall.area-b.index'],
            ['code' => 'C', 'name' => mapArea('C'), 'route' => 'skyfall.area-c.index'],
            ['code' => 'D', 'name' => mapArea('D'), 'route' => 'skyfall.area-d.index'],
            ['code' => 'E', 'name' => mapArea('E'), 'route' => 'skyfall.area-e.index'],
            ['code' => 'F', 'name' => mapArea('F'), 'route' => 'skyfall.area-f.index'],
            ['code' => 'G', 'name' => mapArea('G'), 'route' => 'skyfall.area-g.index'],
            ['code' => 'H', 'name' => mapArea('H'), 'route' => 'skyfall.area-h.index'],
        ];

        return array_merge(parent::share($request), [
            'user.roles' => $request->user() ? $request->user()->roles->pluck('name') : [],
            'user.permissions' => $request->user() ? $request->user()->getPermissionsViaRoles()->pluck('name') : [],
            'appEnv' => env('APP_ENV', 'production'),
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
            ],
            'errors' => fn () => $request->session()->get('errors') ? $request->session()->get('errors')->all() : (object) [],
            'locale' => $locale,
            'supportedLocales' => fn () => $supportedLocales,
            'user.approvedIdentities' => $approvedIdentities,
            'user.invitedIdentities' => $invitedIdentities,
            'identityMenus' => config('identity_menus', []),
            'areas' => $areas,
            'invitationsCount' => $invitationsCount,
        ]);
    }
}