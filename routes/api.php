<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\AuthController;

// 認証不要（公開）
Route::prefix('auth')->group(function () {
    Route::post('register/email', [AuthController::class, 'requestEmail']);
    Route::post('register/verify', [AuthController::class, 'verifyToken']);
    Route::post('register/complete', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// 認証必須
Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    // コメントの作成・更新・削除（ログイン必須）
    Route::prefix('articles/{article}')->group(function () {
        Route::post('comments', [CommentController::class, 'store'])->name('comments.store');
        Route::put('comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
        Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    });
});

// コメント一覧だけは誰でもOK
Route::prefix('articles/{article}')->group(function () {
    Route::get('comments', [CommentController::class, 'index'])->name('comments.index');
});
