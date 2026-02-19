<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BlogCategoryController;
use App\Http\Controllers\API\BlogPostController;
use App\Http\Controllers\API\StudentApiController;
use App\Http\Controllers\API\TestApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/test', [TestApiController::class, 'test'])->name('test-api');


Route::apiResource('/students', StudentApiController::class);

Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::group(['middleware' => 'auth:sanctum'], function(){
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    // Blog Category routes
    Route::apiResource('categories', BlogCategoryController::class)->middleware(['role:admin']);

    // Blog Post routes
    Route::apiResource('posts', BlogPostController::class)->middleware(['role:admin, author']);
    Route::post('blog-post-image/{post}', [BlogPostController::class, 'blogPostImage'])->name('blog-post-image')->middleware(['role:admin, author']);

});

// Get all the categories
Route::get('categories', [BlogCategoryController::class, 'index']);
Route::get('categories/{category}', [BlogCategoryController::class, 'show']);

// Get all the posts
Route::get('posts', [BlogPostController::class, 'index']);
Route::get('posts/{post}', [BlogPostController::class, 'show']);

