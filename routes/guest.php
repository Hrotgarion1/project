<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserIdentitiesController;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use App\Http\Controllers\SummaryAreaController;
use App\Http\Controllers\AreaController;

/*
|--------------------------------------------------------------------------
| Rutas para invitados (sin autenticación específica aún)
|--------------------------------------------------------------------------
| Estas rutas están protegidas por el middleware CheckPermission para el rol "invitado".
*/
Route::middleware(['App\Http\Middleware\CheckPermission:invitado'])->group(function () {
    // Ejemplo: Route::get('/guest-info', [SomeController::class, 'info'])->name('guest.info');
});

/*
|--------------------------------------------------------------------------
| Rutas autenticadas simples (solo requieren autenticación)
|--------------------------------------------------------------------------
| Rutas que solo necesitan que el usuario esté autenticado, sin Sanctum ni verificación adicional.
*/
Route::middleware('auth')->group(function () {
    // Vista de "Mis solicitudes" para identidades (movida a UserIdentitiesController)
    Route::get('/identities/my-requests', [UserIdentitiesController::class, 'index'])
        ->name('identities.myRequests'); // Renombrada para consistencia con 'my-requests.index'

    // Vista de "Mis solicitudes" (alternativa ya correcta)
    Route::get('/my-requests', [UserIdentitiesController::class, 'index'])
        ->name('my-requests.index');
});

/*
|--------------------------------------------------------------------------
| Rutas autenticadas con Sanctum y Jetstream
|--------------------------------------------------------------------------
| Estas rutas requieren autenticación con Sanctum, sesión de Jetstream y verificación de email.
*/
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    /*
    |-----------------------
    | Rutas del dashboard y recursos generales
    |-----------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])
        ->name('dashboard');


    /*
    |-----------------------
    | Rutas de identidades - Índice y roles (para invitados)
    |-----------------------
    */
    // Índice de solicitudes de identidad (movido a UserIdentitiesController)
    Route::get('/identity-request', [UserIdentitiesController::class, 'index'])
        ->name('identity.request');

    // Lista de roles disponibles (API, sin cambios por ahora)
    Route::get('/roles', function () {
        return response()->json(['roles' => Spatie\Permission\Models\Role::pluck('name')]);
    })->name('roles.index');

    /*
    |-----------------------
    | Rutas de identidades - Formulario y creación con límite (para invitados)
    |-----------------------
    */
    // Mostrar formulario de solicitud de identidad (movido a UserIdentitiesController)
    Route::get('/identities/request/{type}', [UserIdentitiesController::class, 'showForm'])
        ->name('identity.request.form');

    // Verificar límite de solicitudes por día (movido a UserIdentitiesController)
    Route::get('/api/check-identity-request-limit', [UserIdentitiesController::class, 'checkLimit'])
        ->name('identity.check.limit');
    //Verificar que ha aceptado los terminos y condiciones
    Route::post('/accept-terms', [UserIdentitiesController::class, 'acceptTerms'])->middleware('auth');

    // Crear nueva solicitud de identidad (movido a UserIdentitiesController)
    Route::middleware('throttle:identity-request')->group(function () {
        Route::post('/identities/request', [UserIdentitiesController::class, 'requestIdentity'])
            ->name('identity.request.store');
    });

    //Ruta para Markdowns de solicitudes de identidad tipos de a a h
    Route::get('/markdown/terms_conditions/{type}', function ($type) {
        $fileName = str_replace(' ', '_', strtolower($type)) . '.md'; // Convierte "tipo F" a "tipo_f.md"
        $path = resource_path("markdown/terms_conditions/{$fileName}"); // Apunta a la subcarpeta "terms"
        Log::info("Buscando archivo en: {$path}");
        if (!file_exists($path)) {
            Log::error("Archivo no encontrado: {$path}");
            abort(404, 'Terms and conditions not found');
        }
        $content = file_get_contents($path);
        $parsedown = new Parsedown();
        $html = $parsedown->text($content); // Convertimos Markdown a HTML
        Log::info("Contenido cargado: " . substr($content, 0, 100));
        return view('terms', ['content' => $html, 'type' => $type]);
    })->name('terms_conditions.show');

    /*
    |-----------------------
    | Rutas de identidades - Gestión dinámica (para invitados)
    |-----------------------
    */
    // Mostrar una identidad específica (movido a UserIdentitiesController)
    Route::get('/user-identities/{identity:slug}', [UserIdentitiesController::class, 'show'])
        ->name('user.identities.show');

    // Editar una identidad (ya estaba correcto en UserIdentitiesController)
    Route::get('/user-identities/{slug}/edit', [UserIdentitiesController::class, 'edit'])
        ->name('user.identities.edit');

    // Actualizar una identidad (ya estaba correcto en UserIdentitiesController)
    Route::post('/user-identities/{identity:slug}', [UserIdentitiesController::class, 'update'])
        ->name('user.identities.update');

    // Eliminar una identidad (movido a UserIdentitiesController)
    Route::delete('/user-identities/{identity:slug}', [UserIdentitiesController::class, 'destroy'])
        ->name('user.identities.destroy');

    // Añadir documentos a una identidad (ya estaba correcto en UserIdentitiesController)
    Route::post('/user-identities/{slug}/documents', [UserIdentitiesController::class, 'storeDocuments'])
        ->name('identities.documents.store');

    // Eliminar un documento específico de una identidad (ya estaba correcto en UserIdentitiesController)
    Route::delete('/user-identities/{slug}/documents/{index}', [UserIdentitiesController::class, 'destroyDocument'])
        ->name('identities.documents.destroy');

    /*
    |-----------------------
    | Rutas de usuarios creados por el admin
    |-----------------------
    */

    Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.update');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::middleware(['auth', 'role:admin'])->post('/users/{email}/regenerate-password-link', [UserController::class, 'regeneratePasswordLink'])->name('users.regenerate-password-link');

    /*
    |-----------------------
    | Rutas de las areas
    |-----------------------
    */
  
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/summary-areas', [SummaryAreaController::class, 'index']);
        Route::post('/summary-areas', [SummaryAreaController::class, 'store']);
        Route::get('/summary-areas/{id}', [SummaryAreaController::class, 'show']);
        Route::get('/areas/{id}', [AreaController::class, 'show']);
    });
});