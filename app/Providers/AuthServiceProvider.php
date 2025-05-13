<?php

namespace App\Providers;

use App\Models\Invitacion;
use App\Models\Identity;
use App\Models\IdentityType;
use App\Policies\InvitacionPolicy;
use App\Policies\IdentityPolicy;
use App\Policies\IdentitytypePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Invitacion::class =>InvitacionPolicy::class, //Policy registrada para el modelo Invitacion
        Identity::class => IdentityPolicy::class,
        IdentityType::class => IdentityTypePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        
        // Definir el Gate para verificar roles
        Gate::define('has-role', function ($user, $role) {
            return $user->hasRole($role);
        });
    }
}
