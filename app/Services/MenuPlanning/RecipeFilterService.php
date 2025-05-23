<?php 

namespace App\Services\MenuPlanning;

use App\Models\Recipe;


class RecipeFilterService {
       /*
       Отправляет кастомный запрос в БД и берет те рецепты, в 
       которых отсутствует максимум 2 ингредиента 
       */

       public function getFilteredRecipes(array $selectedIngredientsIds) {
              $filteredRecipes = Recipe::select('recipes.*')
                     ->join('ingredient_recipe', 'recipes.id', '=', 'ingredient_recipe.recipe_id')
                     ->leftJoin('ingredients', 'ingredients.id', '=', 'ingredient_recipe.ingredient_id')
                     ->groupBy('recipes.id')
                     ->havingRaw('SUM(CASE WHEN ingredients.id NOT IN (' . implode(',', $selectedIngredientsIds ?: [0]) . ') THEN 1 ELSE 0 END) <= 2')
                     ->with('ingredients')
                     ->get();

              return $filteredRecipes;
       }
}