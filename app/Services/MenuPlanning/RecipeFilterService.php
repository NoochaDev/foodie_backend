<?php 

namespace App\Services\MenuPlanning;

use App\Models\IngredientReplacement;
use App\Models\Recipe;


class RecipeFilterService {
       /**
        * Отправляет кастомный запрос в БД и берет нужные рецепты
        * (ингредиенты которых не забанены + хотя бы 2 ингредиента из выбранных)
        * @param array $selectedIngredientIds Массив ID выбранных ингредиентов
        * @param array $bannedIngredientIds Массив ID забаненных ингредиентов
        * @return \Illuminate\Database\Eloquent\Collection Коллекция отфильтрованных рецептов
        */
       public function getFilteredRecipes(array $selectedIngredientIds, array $bannedIngredientIds = []) {
              $selectedIds = implode(',', $selectedIngredientIds ?: [0]);
              $bannedIds = implode(',', $bannedIngredientIds ?: [0]);

              // Сначала получаем рецепты с ингредиентами, которые либо не в banned, либо если в banned - есть замена
              $filteredRecipes = Recipe::select('recipes.*')
                     ->join('ingredient_recipe', 'recipes.id', '=', 'ingredient_recipe.recipe_id')
                     ->leftJoin('ingredients', 'ingredients.id', '=', 'ingredient_recipe.ingredient_id')
                     ->leftJoin('ingredient_replacements', function ($join) {
                     $join->on('ingredient_replacements.recipe_id', '=', 'recipes.id')
                            ->on('ingredient_replacements.original_ingredient_id', '=', 'ingredients.id');
                     })
                     ->groupBy('recipes.id')
                     ->havingRaw("
                            SUM(
                                   CASE
                                          WHEN ingredients.id IN ($bannedIds)
                                                 AND ingredient_replacements.alternative_ingredient_id IS NULL
                                          THEN 1
                                          ELSE 0
                                   END
                            ) <= 2
                     ")
                     ->havingRaw("
                            SUM(
                                   CASE
                                          WHEN ingredients.id IN ($bannedIds)
                                                 AND ingredient_replacements.alternative_ingredient_id IS NULL
                                          THEN 1
                                          ELSE 0
                                   END
                            ) = 0
                     ")
                     ->with(['ingredients', 'ingredientReplacements'])
                     ->get();

              // Дополнительно можно фильтровать по selectedIngredientIds - оставить рецепты, где есть хотя бы 2 из выбранных
              $filteredRecipes = $filteredRecipes->filter(function ($recipe) use ($selectedIngredientIds) {
                     $countSelected = 0;
                     foreach ($recipe->ingredients as $ingredient) {
                            if (in_array($ingredient->id, $selectedIngredientIds)) {
                                   $countSelected++;
                            }
                     }
                     return $countSelected >= 2;
              });

              return $filteredRecipes->values();
       }
}