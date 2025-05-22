<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class IngredientRecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ingredientIds = DB::table('ingredients')->pluck('id')->toArray();
        $recipeIds = DB::table('recipes')->pluck('id')->toArray();

        foreach ($recipeIds as $recipeId) {
            // Выбираем от 2 до 5 случайных ингредиентов для рецепта
            $usedIngredientIds = collect($ingredientIds)->random(rand(2, 5))->toArray();

            foreach ($usedIngredientIds as $ingredientId) {
                DB::table('ingredient_recipe')->insert([
                    'ingredient_id' => $ingredientId,
                    'recipe_id' => $recipeId,
                    'amount' => rand(30, 150), // количество в граммах
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
