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
              // $scoredRecipes = $this->scoreRecipes($recipesByTime, $targetCalories, $targetProtein, $targetFat, $targetCarbs);
// 
              // $topRecipes = $scoredRecipes->sortBy('score')->take(3)->values();
              // $primary = $topRecipes->random()['recipe'];
// 
              // $coverage = $this->calculateCoverage($primary->nutrients, $targetCalories, $targetProtein, $targetFat, $targetCarbs);
// 
              // $menuRecipes = [$primary];
              // if ($coverage < 0.65) {
              //        $additional = $this->findAdditionalRecipe(
              //               $recipesByTime,
              //               $primary,
              //               $targetCalories,
              //               $targetProtein,
              //               $targetFat,
              //               $targetCarbs
              //        );
// 
              //        if ($additional) {
              //               $menuRecipes[] = $additional;
              //        }
              // }
              // return $this->buildMenuEntries($menuRecipes, $userId, $day, $time);

              // Выбираем первый рецепт случайно
              $randomRecipe = $recipesByTime->random();

              $recipes = [];

              $diff_target = $targetCalories / $randomRecipe->nutrients['calories'];
              $additional_grams = $diff_target * 100;

              if ($additional_grams > 300) {
                     $additional_grams = 300;
              }

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

       protected function scoreRecipes(Collection $recipes, float $targetCalories, float $targetProtein, float $targetFat, float $targetCarbs): Collection
       {
              return $recipes->map(function ($recipe) use ($targetCalories, $targetProtein, $targetFat, $targetCarbs) {
                     $nutrients = $recipe['nutrients'];

                     $score = $this->calculateScoreForRecipe($nutrients, $targetCalories, $targetProtein, $targetFat, $targetCarbs);

                     return ['recipe' => $recipe, 'score' => $score];
              });
       }

       protected function calculateScoreForRecipe(array $nutrients, float $targetCalories, float $targetProtein, float $targetFat, float $targetCarbs): float
       {
              return abs(($nutrients['calories'] - $targetCalories) / $targetCalories)
                     + abs(($nutrients['protein'] - $targetProtein) / $targetProtein)
                     + abs(($nutrients['fat'] - $targetFat) / $targetFat)
                     + abs(($nutrients['carbohydrates'] - $targetCarbs) / $targetCarbs);
       }

       protected function calculateCoverage(array $nutrients, float $targetCalories, float $targetProtein, float $targetFat, float $targetCarbs): float
       {
              return (
                     ($nutrients['calories'] / $targetCalories) +
                     ($nutrients['protein'] / $targetProtein) +
                     ($nutrients['fat'] / $targetFat) +
                     ($nutrients['carbohydrates'] / $targetCarbs)
              ) / 4;
       }

       protected function calculatePortionFactor(array $nutrients, float $targetCalories): float
       {
              if ($nutrients['calories'] <= 0) return 0;
              
              return min(1.5, max(0.5, $targetCalories / $nutrients['calories']));
       }

       protected function findAdditionalRecipe(
              Collection $recipes,
              $primary,
              float $targetCalories,
              float $targetProtein,
              float $targetFat,
              float $targetCarbs
       ) {
              $primaryNutrients = $primary->nutrients;

              return $recipes->filter(fn($r) => $r->id !== $primary->id)
              ->map(function ($r) use ($primaryNutrients, $targetCalories, $targetProtein, $targetFat, $targetCarbs) {
                     $nutrients = $r->nutrients;

                     $combined = [
                            'calories' => $nutrients['calories'] + $primaryNutrients['calories'],
                            'protein' => $nutrients['protein'] + $primaryNutrients['protein'],
                            'fat' => $nutrients['fat'] + $primaryNutrients['fat'],
                            'carbohydrates' => $nutrients['carbohydrates'] + $primaryNutrients['carbohydrates'],
                     ];

                     $score = $this->calculateScoreForRecipe($combined, $targetCalories, $targetProtein, $targetFat, $targetCarbs);

                     return ['recipe' => $r, 'score' => $score];
              })
              ->sortBy('score')
              ->first()['recipe'] ?? null;
       }

       protected function buildMenuEntries(array $recipes, int $userId, int $day, int $time): array
       {
              return collect($recipes)->map(function ($recipe) use ($userId, $day, $time) {
                            return [
                                   'user_id' => $userId,
                                   'recipe_id' => $recipe->id,
                                   'day' => $day,
                                   'time' => $time,
                                   'created_at' => now(),
                                   'updated_at' => now(),
                            ];
                     })->toArray();
              }
       }
