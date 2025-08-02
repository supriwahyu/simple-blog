<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostDetailController;
use App\Http\Controllers\LoginController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [LoginController::class, 'login']);
Route::get('/post/all', [PostController::class, 'get']);
Route::get('/post/{id}', [PostController::class, 'getId']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout']);

    Route::group(['prefix' => '/master'], function () {
        Route::controller(PostController::class)->group(function () {
            Route::get('/post/index', 'index');
            Route::get('/post/show/{id}', 'show');
            Route::get('/post/all', 'get');
            Route::post('/post/store', 'store');
            Route::post('/post/update/{id}', 'update');
            Route::delete('/post/destroy/{id}', 'destroy');
        });
        Route::controller(PostDetailController::class)->group(function () {
            Route::get('/post-detail/index', 'index');
            Route::get('/post-detail/show/{id}', 'show');
            Route::get('/post-detail/all', 'get');
            Route::post('/post-detail/store', 'store');
            Route::put('/post-detail/update/{id}', 'update');
            Route::delete('/post-detail/destroy/{id}', 'destroy');
        });
    });
});

