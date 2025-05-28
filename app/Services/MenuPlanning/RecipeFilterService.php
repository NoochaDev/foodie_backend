<?php 

namespace App\Services\MenuPlanning;

use App\Models\Recipe;


class RecipeFilterService {
       /*
       Отправляет кастомный запрос в БД и берет те рецепты, в 
       которых отсутствует максимум 2 ингредиента 
       */

       public function getFilteredRecipes(array $selectedIngredientIds, array $bannedIngredientIds = []) {
              $selectedIds = implode(',', $selectedIngredientIds ?: [0]);
              $bannedIds = implode(',', $bannedIngredientIds ?: [0]);

              $filteredRecipes = Recipe::select('recipes.*')
                     ->join('ingredient_recipe', 'recipes.id', '=', 'ingredient_recipe.recipe_id')
                     ->leftJoin('ingredients', 'ingredients.id', '=', 'ingredient_recipe.ingredient_id')
                     ->groupBy('recipes.id')
                     // 1. Разрешаем максимум 2 пропущенных ингредиента
                     ->havingRaw("
                            SUM(CASE 
                                   WHEN ingredients.id NOT IN ($selectedIds) THEN 1 
                                   ELSE 0 
                            END) <= 2
                     ")
                     // 2. Исключаем рецепты, где есть хоть 1 забаненный ингредиент
                     ->havingRaw("
                            SUM(CASE 
                                   WHEN ingredients.id IN ($bannedIds) THEN 1 
                                   ELSE 0 
                            END) = 0
                     ")
              ->with('ingredients')
              ->get();

              return $filteredRecipes;
       }
}