<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\V1\RecipesSelectionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::get('/recipes', [RecipesSelectionController::class, 'index'])->name('recipes.index');
    Route::get('/getFitRecipes', [RecipesSelectionController::class, 'selectRecipes'])->name('recipes.fit.get');
});