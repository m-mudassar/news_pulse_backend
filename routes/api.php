<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\SourcesController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::get('logout', [AuthController::class, 'logout']);
    Route::get('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
});

Route::group(['middleware' => 'auth:api',], function ($router) {
    Route::get('/preferences', [PreferenceController::class, 'getPreferences']);
    Route::post('/preferences', [PreferenceController::class, 'updatePreferences']);

    Route::post('/news', [NewsController::class, 'index']);

    Route::get('/sources', [SourcesController::class, 'index']);
    Route::get("/search/sources", [SourcesController::class, 'search']);
    Route::patch('/user/sources', [SourcesController::class, 'updateUserSources']);

    Route::get('/authors', [AuthorsController::class, 'index']);
    Route::get("/search/authors", [AuthorsController::class, 'search']);
    Route::patch('/user/authors', [AuthorsController::class, 'updateUserAuthors']);

    Route::get('/categories', [CategoriesController::class, 'index']);
    Route::get("/search/categories", [CategoriesController::class, 'search']);
    Route::patch('/user/categories', [CategoriesController::class, 'updateUserCategories']);


});

