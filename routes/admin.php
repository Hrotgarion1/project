<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PaisController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\IdentityActionReasonController;
use App\Http\Controllers\IdentityRequestController;
use App\Http\Controllers\IdentityPanelController;
use App\Http\Controllers\SuspensionRulesController;
use App\Http\Controllers\WebIdentityTypeController;

/*
|--------------------------------------------------------------------------
| Rutas protegidas para admin (middleware CheckPermission)
|--------------------------------------------------------------------------
| Estas rutas solo son accesibles para usuarios con el rol "admin" mediante el middleware CheckPermission.
| Actualmente vacío, pero se pueden añadir recursos específicos si es necesario.
*/
Route::middleware(['App\Http\Middleware\CheckPermission:admin'])->group(function () {
    // Ejemplo: Route::resource('roles', RolesController::class);
    // Ejemplo: Route::get('permisos', [Seguridad\PermisosController::class, 'index'])->name('permisos.index');
});

/*
|--------------------------------------------------------------------------
| Rutas autenticadas con Sanctum y Jetstream
|--------------------------------------------------------------------------
| Estas rutas requieren autenticación completa con Sanctum, sesión de Jetstream y verificación de email.
| Incluyen recursos CRUD generales y acciones específicas para admin.
*/
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    /*
    |-----------------------
    | Recursos CRUD generales
    |-----------------------
    */
    Route::get('/paises', [PaisController::class, 'index'])->name('paises.index');
   Route::get('/paises/create', [PaisController::class, 'create'])->name('paises.create');
   Route::post('/paises', [PaisController::class, 'store'])->name('paises.store');
   Route::get('/paises/{pais}', [PaisController::class, 'show'])->name('paises.show');
   Route::get('/paises/{pais}/edit', [PaisController::class, 'edit'])->name('paises.edit');
   Route::put('/paises/{pais}', [PaisController::class, 'update'])->name('paises.update');
   Route::delete('/paises/{pais}', [PaisController::class, 'destroy'])->name('paises.destroy');

    Route::get('/roles', [UserController::class, 'getRoles'])->name('roles.index');
    Route::get('/countries', [UserController::class, 'getCountries'])->name('countries.index');

    Route::resource('/categories', CategoryController::class)
        ->names('categories'); // Recursos para gestión de categorías

    Route::resource('/users', UserController::class)
        ->names('users'); // Recursos para gestión de usuarios

    /*
    |-----------------------
    | Rutas de identidades (gestión general)
    |-----------------------
    /*
    |--------------------------------------------------------------------------
    | Rutas para administradores, supervisores y editores
    |--------------------------------------------------------------------------
    */
    Route::get('/identity-panel', [IdentityPanelController::class, 'index'])
    ->middleware(['auth', 'role:admin|supervisor|editor'])
    ->name('identity-panel.index');

    /*
    |-----------------------
    | Acciones exclusivas para admin y supervisores
    |-----------------------
    */
    Route::middleware(['auth', 'role:admin|supervisor'])->group(function () {
    Route::post('/identities/{identity:slug}/reassign', [IdentityRequestController::class, 'reassign'])
        ->name('identities.reassign');
    Route::post('/identities/bulk-reassign', [IdentityRequestController::class, 'bulkReassign'])
        ->name('identities.bulk-reassign');
    });

    /*
    |-----------------------
    | Acciones exclusivas para admin
    |-----------------------
    */
    Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('/identities/{slug}/restore', [IdentityRequestController::class, 'restore'])
        ->name('identities.restore');
    });

    Route::middleware(['auth', 'role:admin'])->resource('identity-types', WebIdentityTypeController::class);

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/suspension-rules', function () {
            return Inertia::render('Admin/SuspensionRules');
        })->name('suspension.rules');
        Route::get('/suspension-rules/data', [SuspensionRulesController::class, 'index'])->name('suspension.rules.index');
        Route::post('/suspension-rules', [SuspensionRulesController::class, 'store'])->name('suspension.rules.store');
        Route::put('/suspension-rules/{rule}', [SuspensionRulesController::class, 'update'])->name('suspension.rules.update');
        Route::delete('/suspension-rules/{rule}', [SuspensionRulesController::class, 'destroy'])->name('suspension.rules.destroy');
        Route::resource('identity-action-reasons', IdentityActionReasonController::class)    ->middleware(['auth', 'verified'])->names('identity-action-reasons');
    });

});

?>