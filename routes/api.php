<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ArticleController;

Route::prefix('v1')->group(function() {
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::post('/articles/preferences', [ArticleController::class, 'preferences']);
    Route::get('/articles/{id}', [ArticleController::class, 'show']);
});
