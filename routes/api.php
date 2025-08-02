<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\LoginController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [LoginController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::group(['prefix' => '/blog'], function () {
        Route::controller(MaterialController::class)->group(function () {
            Route::get('/blog/collections/{collections}', 'index');
            Route::get('/blog/show/{id}', 'show');
            Route::get('/blog/all', 'get');
            Route::post('/blog/store', 'store');
            Route::put('/blog/update/{id}', 'update');
            Route::delete('/blog/destroy/{id}', 'destroy');
        });
    });
});

