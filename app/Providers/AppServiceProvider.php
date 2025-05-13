<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\Identity;
use App\Observers\IdentityObserver;
use App\Models\AreaRecord;
use App\Observers\AreaRecordObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    // Forzar HTTPS en producción
    if (app()->environment('production')) {
        URL::forceScheme('https');
    }

    // Limitar a 20 solicitudes por día por usuario/IP
    RateLimiter::for('identity-request', function (Request $request) {
        return Limit::perDay(20)->by(optional($request->user())->id ?: $request->ip());
    });

    // Registrar observers
    Identity::observe(IdentityObserver::class);
    AreaRecord::observe(AreaRecordObserver::class);
}
}
