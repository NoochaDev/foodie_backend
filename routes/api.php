<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\V1\MenuController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::get('/recipes', [MenuController::class, 'index'])->name('recipes.index');
    Route::get('/getFitRecipes', [MenuController::class, 'selectRecipes'])->name('recipes.fit.get');
    Route::get('/getDailyMenu', [MenuController::class, 'dailyMenu'])->name('recipes.daily.menu');
});