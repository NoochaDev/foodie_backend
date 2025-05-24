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

              // Допустимый разброс
              $tol = 0.1;

              // Выбираем первый рецепт случайно
              $recipe1 = $recipesByTime->random();
              $n1 = $recipe1;

              // Вычисляем диапазоны коэффициента для каждого нутриента
              $ranges = [
                     'cal' => [ ($targetCalories*(1-$tol)) / $recipe1->nutrients['calories'], ($targetCalories*(1+$tol)) / $recipe1->nutrients['calories'] ],
                     'prot'=> [ ($targetProtein*(1-$tol))  / $recipe1->nutrients['protein'],  ($targetProtein*(1+$tol))  / $recipe1->nutrients['protein'] ],
                     'fat' => [ ($targetFat*(1-$tol))      / $recipe1->nutrients['fat'],      ($targetFat*(1+$tol))      / $recipe1->nutrients['fat'] ],
                     'carb'=> [ ($targetCarbs*(1-$tol))    / $recipe1->nutrients['carbohydrates'],    ($targetCarbs*(1+$tol))    / $recipe1->nutrients['carbohydrates'] ],
              ];

              // Пересечение диапазонов
              $minX = max($ranges['cal'][0], $ranges['prot'][0], $ranges['fat'][0], $ranges['carb'][0], 0.5);
              $maxX = min($ranges['cal'][1], $ranges['prot'][1], $ranges['fat'][1], $ranges['carb'][1], 2.0);
              $results = [];
              if ($minX <= $maxX) {
                     // Один рецепт покрывает цели
                     $factor = ($minX + $maxX) / 2;
                     $results[] = [
                     'user_id' => $userId,
                     'recipe_id' => $recipe1->id,
                     'day' => $day,
                     'time' => $time,
                     'portion_factor' => $factor
                     ];
                     return $results;
              }
              // Если одного рецепта недостаточно, подбираем второй
              $recipe2 = $recipesByTime->where('id', '!=', $recipe1->id)->random();
              $n2 = $recipe2;

              // Решаем систему для двух рецептов. Пример: решение по калориям и белкам.
              // a1*X + b1*Y = targetCalories, a2*X + b2*Y = targetProtein
              $a1 = $n1->nutrients['calories']; $b1 = $n2->nutrients['calories'];
              $a2 = $n1->nutrients['protein'];  $b2 = $n2->nutrients['protein'];
              $det = $a1 * $b2 - $a2 * $b1;
              if (abs($det) > 1e-6) {
                     // Решение линейной системы
                     $X = ($targetCalories * $b2 - $targetProtein * $b1) / $det;
                     $Y = ($a1 * $targetProtein - $a2 * $targetCalories) / $det;
                     // Проверяем допустимость коэффициентов
                     if ($X >= 0.5 && $X <= 2.0 && $Y >= 0.5 && $Y <= 2.0) {
                     // Проверяем остальные нутриенты с учетом погрешности
                     $totalFat = $n1->nutrients['fat'] * $X + $n2->nutrients['fat'] * $Y;
                     $totalCarb= $n1->nutrients['carbohydrates']* $X + $n2->nutrients['carbohydrates']* $Y;
                     if ($totalFat >= $targetFat*(1-$tol) && $totalFat <= $targetFat*(1+$tol)
                     && $totalCarb >= $targetCarbs*(1-$tol) && $totalCarb <= $targetCarbs*(1+$tol)) {
                            // Добавляем оба рецепта в результат
                            $results[] = [
                                   'user_id' => $userId,
                                   'recipe_id' => $recipe1->id,
                                   'day' => $day,
                                   'time' => $time,
                                   'portion_factor' => $X
                            ];
                            $results[] = [
                                   'user_id' => $userId,
                                   'recipe_id' => $recipe2->id,
                                   'day' => $day,
                                   'time' => $time,
                                   'portion_factor' => $Y
                            ];

                            return $results;
                     }
                     }
              }
              // При неудаче можно повторить попытку с другими рецептами или обработать ошибку
              return [];
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
