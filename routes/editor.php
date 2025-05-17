<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PaisController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\IdentityRequestController;

/*
|--------------------------------------------------------------------------
| Rutas protegidas para editores (middleware CheckPermission)
|--------------------------------------------------------------------------
| Estas rutas solo son accesibles para usuarios con el rol "editor".
| Actualmente vacío, pero se pueden añadir recursos específicos si es necesario.
*/
Route::middleware(['App\Http\Middleware\CheckPermission:editor'])->group(function () {
    // Ejemplo: Route::resource('roles', RolesController::class);
    // Ejemplo: Route::get('permisos', [Seguridad\PermisosController::class, 'index'])->name('permisos.index');
});

/*
|--------------------------------------------------------------------------
| Rutas para admin y editores
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin|editor'])->group(function () {
    Route::resource('/identities', IdentityRequestController::class)
        ->only(['index', 'show'])
        ->names([
            'index' => 'admin.identities.index',
            'show' => 'admin.identities.show',
        ])
        ->parameters(['identities' => 'identity:slug']);

    Route::post('/identities/{identity:slug}/status', [IdentityRequestController::class, 'updateStatus'])
        ->name('identities.updateStatus');
    Route::post('/identities/{identity:slug}/suspend', [IdentityRequestController::class, 'suspend'])
        ->name('identities.suspend');
    Route::post('/identities/{identity:slug}/reactivate', [IdentityRequestController::class, 'reactivate'])
        ->name('identities.reactivate');
    Route::post('/identities/{identity:slug}/take', [IdentityRequestController::class, 'takeRequest'])
        ->name('identities.takeRequest');
    Route::delete('/identities/{identity:slug}', [IdentityRequestController::class, 'destroy'])
        ->name('identities.destroy');
    Route::post('/identities/{identity:slug}/request-more-docs', [IdentityRequestController::class, 'requestMoreDocs'])
        ->name('identities.request-more-docs');

    Route::get('/admin/identities/my-requests', [IdentityRequestController::class, 'myRequests'])
        ->name('admin.my-requests.index');
    Route::get('/users/handlers', [UserController::class, 'getHandlers'])
        ->name('users.handlers');
});

/*
|--------------------------------------------------------------------------
| Rutas autenticadas con Sanctum y Jetstream (admin/editor)
|--------------------------------------------------------------------------
| Rutas que requieren autenticación completa, usadas por admin y editores para recursos generales.
*/
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // Recursos CRUD generales
    //Route::resource('/paises', PaisController::class)->names('paises'); // Gestión de países

    Route::resource('/categories', CategoryController::class)
        ->names('categories'); // Gestión de categorías

    Route::resource('/users', UserController::class)
        ->names('users'); // Gestión de usuarios

});

?>