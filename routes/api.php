<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Api\IdentityTypeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/roles', function () {
    $roles = Role::pluck('name'); // Obtiene solo los nombres de los roles
    return response()->json(['roles' => $roles]);
});

Route::get('/required-documents/{type}', [IdentityTypeController::class, 'getRequiredDocuments']);
