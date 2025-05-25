<?php

namespace App\Services\MenuPlanning;

use Illuminate\Support\Collection;

class RecipeSelectorService
{
       public function selectMealForTimeSlot(
              Collection $recipesByTime,
              float $targetCalories,
              float $targetProtein,
              float $targetFat,
              float $targetCarbs,
              int $userId,
              int $day,
              int $time
       ): array 
       {
              // Выбираем первый рецепт случайно
              $randomRecipe = $recipesByTime->random();

              $recipes = [];

              $additional_grams = $this->calculateGrams($randomRecipe, $targetCalories);

              $recipes[] = [
                     'user_id' => $userId,
                     'recipe_id' => $randomRecipe->id,
                     'additional_grams' => $additional_grams,
                     'day' => $day,
                     'time' => $time,
                     'created_at' => now(),
                     'updated_at' => now(),
              ];

              // Если первого рецепта не хватает даже в 300 граммах
              $caloriesFromFirst = $randomRecipe->nutrients['calories'] * ($additional_grams / 100);

              if ($caloriesFromFirst < $targetCalories * 0.9) {
                     // Ищем второй рецепт, исключая первый
                     $remainingRecipes = $recipesByTime->reject(fn($r) => $r->id === $randomRecipe->id);

                     if ($remainingRecipes->isNotEmpty()) {
                            $randomRecipe2 = $remainingRecipes->random();

                            $remainingCalories = $targetCalories - $caloriesFromFirst;
                            $diff_target2 = $remainingCalories / $randomRecipe2->nutrients['calories'];
                            $additional_grams2 = min($diff_target2 * 100, 300); // но не больше 300 грамм

                            $recipes[] = [
                                   'user_id' => $userId,
                                   'recipe_id' => $randomRecipe2->id,
                                   'additional_grams' => $additional_grams2,
                                   'day' => $day,
                                   'time' => $time,
                                   'created_at' => now(),
                                   'updated_at' => now(),
                            ];
                     }
              }

              return $recipes;
       }

       private function calculateGrams($recipe, float $targetCalories) {
              $grams = ($targetCalories / $recipe->nutrients['calories']) * 100;
              return min($grams, 300);
       }
}