<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;

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