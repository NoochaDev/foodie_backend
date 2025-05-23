<?php

namespace App\Services\MenuPlanning;


class MenuScorerService {
       /*
       Подрибаются рецепты по дням, завтраку, обеду, ужину
       Подбор идет по очкам (чем меньше очков, тем лучше результат => выбираются 
       рецепты с наименьшим кол-вом очков по целевым нутриентам)
       */

       public function calculateScore($recipesByTime, $targetCalories, 
              $targetProtein, 
              $targetFat, 
              $targetCarbs, $userId,
              $day, $time) {
              $weeklyMenu = [];

              $recipesWithScores = $recipesByTime->map(function($recipe) use ($targetCalories, 
                     $targetProtein, $targetFat, $targetCarbs) {                     
                     $nutrients = $recipe->nutrients;

                     $score = abs(($nutrients['calories'] - $targetCalories) / $targetCalories)
                            + abs(($nutrients['protein'] - $targetProtein) / $targetProtein)
                            + abs(($nutrients['fat'] - $targetFat) / $targetFat)
                            + abs(($nutrients['carbohydrates'] - $targetCarbs) / $targetCarbs);
                            
                     return ['recipe' => $recipe, 'score' => $score];
              });

              $sorted = $recipesWithScores->sortBy('score')->values();
              $topN = $sorted->take(3);
              $chosen = $topN->random();

              $primary = $chosen['recipe'];
              $primaryNutrients = $primary->nutrients;

              // Проверка покрытия нутриентов
              $coverage = (
                     ($primaryNutrients['calories'] / $targetCalories) +
                     ($primaryNutrients['protein'] / $targetProtein) +
                     ($primaryNutrients['fat'] / $targetFat) +
                     ($primaryNutrients['carbohydrates'] / $targetCarbs)
              ) / 4;

              $recipeIds = [$primary->id];

              // Если покрытие меньше 85%, подбираем второе блюдо
              if ($coverage < 0.65) {
                     $additional = $recipesByTime->filter(fn($r) => $r->id !== $primary->id)
                            ->map(function ($r) use ($primaryNutrients, $targetCalories, $targetProtein, $targetFat, $targetCarbs) {
                                   $nutrients = $r->nutrients;
                                   $combined = [
                                          'calories' => $nutrients['calories'] + $primaryNutrients['calories'],
                                          'protein' => $nutrients['protein'] + $primaryNutrients['protein'],
                                          'fat' => $nutrients['fat'] + $primaryNutrients['fat'],
                                          'carbohydrates' => $nutrients['carbohydrates'] + $primaryNutrients['carbohydrates'],
                                   ];

                                   $score = abs(($combined['calories'] - $targetCalories) / $targetCalories)
                                          + abs(($combined['protein'] - $targetProtein) / $targetProtein)
                                          + abs(($combined['fat'] - $targetFat) / $targetFat)
                                          + abs(($combined['carbohydrates'] - $targetCarbs) / $targetCarbs);
                                   
                                   return ['recipe' => $r, 'score' => $score];
                            })
                     ->sortBy('score')
                     ->first();

                     if ($additional) {
                            $recipeIds[] = $additional['recipe']->id;
                     }

                     /*
                     Создать пивот-таблицу ingredient_recipe_user (
                            $user_id, $recipe_id, $ingredient_id, $amount
                     ) в которую можно будет добавлять
                     кастомные граммовки для пользователя на тот или иной ингредиент рецепта который есть в меню пользователя, 
                     изменить отображение меню
                     */
                     
                     foreach ($recipeIds as $recipeId) {
                            $weeklyMenu[] = [
                                   'user_id' => $userId,
                                   'recipe_id' => $recipeId,
                                   'day' => $day,
                                   'time' => $time,
                                   'created_at' => now(),
                                   'updated_at' => now(),
                            ];
                     }
              }

              return $weeklyMenu;
       }
}