<?php

namespace App\Services;

use App\Models\Recipe;

class MenuPlanner {
       public function generateWeeklyMenu(array $targets, float $tolerance = 0.1): array
       {
              $allRecipes = Recipe::with('ingredients')->get();

              $recipes = $allRecipes->map(function ($recipe) {
                     return [
                            'recipe' => $recipe, 
                            'nutrients' => $recipe->nutrients
                     ];
              });

              $days = [];
              $usedRecipeIds = [];

              for ($day = 0; $day < 7; $day++) {
                     $combo = $this->findBestCombination($recipes, $targets, $tolerance, $usedRecipeIds);

                     if ($combo) {
                            $days[] = $combo;
                            foreach ($combo as $item) {
                            $usedRecipeIds[] = $item['recipe']->id;
                            }
                     } else {
                            break;
                     }
              }

              return $days;
       }

       private function findBestCombination(Collection $recipes, array $targets, float $tolerance, array $excludeIds): ?array
       {
              $candidates = $recipes->reject(fn($r) => in_array($r['recipe']->id, $excludeIds))->values();
              $combinations = $candidates->combinations(3);

              $best = null;
              $minDiff = INF;

              foreach ($combinations as $combo) {
                     $total = ['protein' => 0, 'fat' => 0, 'carbohydrates' => 0, 'calories' => 0];

                     foreach ($combo as $item) {
                            foreach ($total as $key => $_) {
                                   $total[$key] += $item['nutrients'][$key];
                            }
              }

              $withinTolerance = true;
              foreach ($targets as $key => $expected) {
                     $actual = $total[$key];
                     $diffRatio = abs($actual - $expected) / $expected;
                     if ($diffRatio > $tolerance) {
                            $withinTolerance = false;
                            break;
                     }
              }

              if ($withinTolerance) {
                     $diffScore = abs($total['calories'] - $targets['calories']);
                     if ($diffScore < $minDiff) {
                            $minDiff = $diffScore;
                            $best = $combo;
                     }
              }
              }

              return $best;
       }  
}
