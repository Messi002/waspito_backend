<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes with Sanctum authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\UserProfileController::class, 'me']);
    Route::post('/posts/{post}/like', [App\Http\Controllers\PostController::class, 'like']);
    Route::delete('/posts/{post}/like/{likeId}', [App\Http\Controllers\PostController::class, 'unlike']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/comments', [CommentController::class, 'index']);
    Route::post('/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    Route::post('/comments/{comment}/likes', [LikeController::class, 'store']);
    Route::delete('/comments/{comment}/likes/{like}', [LikeController::class, 'destroy']);

    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{post}/comments', [PostController::class, 'forPost']);
    Route::delete('/posts/{post}/comments/{comment}', [CommentController::class, 'destroyForPost']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);

});

// Hybrid authentication for users endpoint (Sanctum or static token)
Route::get('/users', [UserController::class, 'index'])->middleware('hybrid.auth');
