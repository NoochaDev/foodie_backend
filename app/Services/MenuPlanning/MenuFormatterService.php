<?php 

namespace App\Services\MenuPlanning;

use App\Models\Recipe;


class MenuFormatterService {
       /*
       Отдает полученное меню на неделю в разных форматах (JSON и т.д.)
       */

       public function getJsonPlan(array $weeklyMenu) {
              $usedRecipeIds = collect($weeklyMenu)->pluck('recipe_id')->unique();
              $recipes = Recipe::whereIn('id', $usedRecipeIds)->get()->keyBy('id');

              $groupedByDay = collect($weeklyMenu)->groupBy('day')->map(function($dayEntries) use ($recipes) {
                     $meals = [
                            1 => 'breakfast',
                            2 => 'lunch',
                            3 => 'dinner',
                     ];

                     $dayResult = [
                            'breakfast' => [],
                            'lunch' => [],
                            'dinner' => [],
                            'totals' => [
                                   'calories' => 0,
                                   'protein' => 0,
                                   'fat' => 0,
                                   'carbohydrates' => 0,
                            ],
                     ];

                     foreach ($dayEntries as $entry) {
                            $mealKey = $meals[$entry['time']] ?? null;
                            if (!$mealKey) continue;

                            $recipe = $recipes[$entry['recipe_id']];
                            $nutrients = $recipe->nutrients;

                            $dayResult[$mealKey][] = [
                                   'id' => $recipe->id,
                                   'title' => $recipe->title,
                                   'meal_type_id' => $recipe->meal_type_id,
                                   'nutrients' => $nutrients,
                            ];

                            $dayResult['totals']['calories'] += $nutrients['calories'] ?? 0;
                            $dayResult['totals']['protein'] += $nutrients['protein'] ?? 0;
                            $dayResult['totals']['fat'] += $nutrients['fat'] ?? 0;
                            $dayResult['totals']['carbohydrates'] += $nutrients['carbohydrates'] ?? 0;
                     }

                     return $dayResult;
              });

              return $groupedByDay;
       }
}