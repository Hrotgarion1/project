<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\TypeAController;
use App\Http\Controllers\TypeBController;
use App\Http\Controllers\TypeCController;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/invitations/sent/{slug}', [InvitationController::class, 'sent'])->name('invitations.sent'); // Enviadas
    Route::get('/invitations/received}', [InvitationController::class, 'received'])->name('invitations.received'); // Recibidas
    Route::get('/invitations', [InvitationController::class, 'index'])->name('invitations.index');
    Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');
    Route::post('/invitations/accept/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');
    Route::get('/invitations/create/{slug}', [InvitationController::class, 'create'])->name('invitations.create');
    Route::delete('/invitations/{id}', [InvitationController::class, 'cancel'])->name('invitations.cancel');

    // Rutas placeholder para probar
    Route::get('/identity/{slug}/dashboard', [TypeAController::class, 'dashboard'])->name('type-a.dashboard');
    Route::get('/type-b/dashboard/{slug}', [TypeBController::class, 'dashboard'])->name('type-b.dashboard');
    Route::get('/type-c/dashboard/{slug}', [TypeCController::class, 'dashboard'])->name('type-c.dashboard');

    // Rutas para media
    Route::post('/media/{mediableType}/{slug}', [MediaController::class, 'store'])->name('media.store');
    Route::delete('/media/{mediableType}/{slug}/{mediaId}', [MediaController::class, 'destroy'])->name('media.destroy');
    Route::post('/media/{mediableType}/{slug}/reorder', [MediaController::class, 'reorder'])->name('media.reorder');
    Route::get('/media/{mediableType}/{identityId}', [MediaController::class, 'index']);
    });