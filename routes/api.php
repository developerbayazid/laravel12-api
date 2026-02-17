<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BlogCategoryController;
use App\Http\Controllers\API\BlogPostController;
use App\Http\Controllers\API\StudentApiController;
use App\Http\Controllers\API\TestApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::get('/test', [TestApiController::class, 'test'])->name('test-api');


Route::apiResource('/students', StudentApiController::class);

Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::group(['middleware' => 'auth:sanctum'], function(){
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    // Blog Category routes
    Route::apiResource('categories', BlogCategoryController::class);

    // Blog Post routes
    Route::apiResource('posts', BlogPostController::class);

});

Route::get('categories', [BlogCategoryController::class, 'index']);

