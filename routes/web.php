<?php

use Illuminate\Foundation\Application;
use App\Http\Controllers\AreaAController;
use App\Http\Controllers\AreaBController;
use App\Http\Controllers\AreaCController;
use App\Http\Controllers\AreaDController;
use App\Http\Controllers\AreaEController;
use App\Http\Controllers\AreaFController;
use App\Http\Controllers\AreaGController;
use App\Http\Controllers\AreaGlobalController;
use App\Http\Controllers\AreaHController;
use App\Http\Controllers\AreaResumenController;
use App\Http\Controllers\BelongingAreaController;
use App\Http\Controllers\PlatformRulesController;
use App\Http\Controllers\PaisController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BelongingController;
use App\Http\Controllers\UserController;

//Para redireccionar la ruta desde otro archivo y que no se amontonen aquí
include __DIR__.'/admin.php';
include __DIR__.'/guest.php';
include __DIR__.'/teams.php';
include __DIR__.'/editor.php';

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', ])->group(function () {
    Route::get('/dashboard', function () { return Inertia::render('Dashboard'); })->name('dashboard');
});

Route::post('/language/switch', [App\Http\Controllers\LanguageController::class, 'switch'])->name('language.switch');
Route::get('/welcome', function () { return Inertia::render('Welcome'); })->name('welcome');
//Rutas para los idiomas
Route::get('/language/{language}', function($language){
    Session::put('locale', $language);
    return redirect()->back();
})->name('language');
//Ruta para las traducciones de sweetalert
Route::get('/lang.json', function () {
    $locale = session('locale', config('app.locale')); // Usa el locale de la sesión
    return response()->json(Lang::getLoader()->load($locale, 'messages'));
})->name('lang.json');

//Rutas no autenticadas
Route::get('/', [DashboardController::class, 'index']);
Route::get('/welcome', function () { return Inertia::render('Welcome'); })->name('welcome');
Route::get('/rules', [PlatformRulesController::class, 'show'])->name('rules.show');

//Ruta para los paises
Route::get('/api/paises', [PaisController::class, 'obtenerPaises']);

Route::get('/messages', [App\Http\Controllers\MessagesController::class, 'send'])->name('messages');


Route::middleware('auth')->group(function () {
    Route::get('/belongings/search', [BelongingController::class, 'search'])->name('belongings.search');
    Route::post('/belongings', [BelongingController::class, 'store'])->name('belongings.store');
});

Route::middleware(['auth', 'verified'])->prefix('skyfall')->group(function () {
    // Área A
    Route::resource('area-a', AreaAController::class)->names('skyfall.area-a');
    Route::post('area-a/{id}/verify', [AreaAController::class, 'verify'])->name('skyfall.area-a.verify');

    // Área B
    Route::resource('area-b', AreaBController::class)->names('skyfall.area-b');
    Route::post('area-b/{id}/verify', [AreaBController::class, 'verify'])->name('skyfall.area-b.verify');

    // Área C
    Route::resource('area-c', AreaCController::class)->names('skyfall.area-c');
    Route::post('area-c/{id}/verify', [AreaCController::class, 'verify'])->name('skyfall.area-c.verify');

    // Área D
    Route::resource('area-d', AreaDController::class)->names('skyfall.area-d');
    Route::post('area-d/{id}/verify', [AreaDController::class, 'verify'])->name('skyfall.area-d.verify');

    // Área E
    Route::resource('area-e', AreaEController::class)->names('skyfall.area-e');
    Route::post('area-e/{id}/verify', [AreaEController::class, 'verify'])->name('skyfall.area-e.verify');

    // Área F
    Route::resource('area-f', AreaFController::class)->names('skyfall.area-f');
    Route::post('area-f/{id}/verify', [AreaFController::class, 'verify'])->name('skyfall.area-f.verify');

    // Área G
    Route::resource('area-g', AreaGController::class)->names('skyfall.area-g');
    Route::post('area-g/{id}/verify', [AreaGController::class, 'verify'])->name('skyfall.area-g.verify');

    // Área H
    Route::resource('area-h', AreaHController::class)->names('skyfall.area-h');
    Route::post('area-h/{id}/verify', [AreaHController::class, 'verify'])->name('skyfall.area-h.verify');

    // Área Resumen
    Route::get('area-resumen', [AreaResumenController::class, 'index'])->name('skyfall.area-resumen');
    Route::get('belonging/{id}', [AreaResumenController::class, 'show'])->name('skyfall.belonging.detail');

    //Area Global
    Route::get('area-global', [AreaGlobalController::class, 'index'])->name('skyfall.area-global');
    Route::get('/area-global/sumas', [AreaGlobalController::class, 'getSumas'])->name('area-global.sumas');
    Route::get('/area-global/areas/{belonging_id}', [AreaGlobalController::class, 'getAreas'])->name('area-global.areas');
    Route::get('/user/preferences', [UserController::class, 'getPreferences'])->name('user.preferences.get');
    Route::post('/user/preferences', [UserController::class, 'savePreferences'])->name('user.preferences.save');
    //Vista BelongingAreaList para ver los registros de un solo area.
    Route::get('skyfall/belonging/{belonging_id}/area/{area_name}/records', [BelongingAreaController::class, 'records'])
    ->name('skyfall.belonging-area-records.index');

});