<?php

namespace App\Services\MenuPlanning;

use App\Enums\MealType as MealTypeEnum;
use App\Models\Recipe;


class MenuPlanner1 {
       protected $mealTimes = [
              MealTypeEnum::BREAKFAST->value,
              MealTypeEnum::LUNCH->value,
              MealTypeEnum::DINNER->value,
       ];

       protected $mealPercents = [
              MealTypeEnum::BREAKFAST->value => 0.3,
              MealTypeEnum::LUNCH->value => 0.4,
              MealTypeEnum::DINNER->value => 0.3,
       ];
       
       public function generateWeeklyMenu(float $targetCalories, int $userId, $recipes) : array {
              $weeklyMenu = [];

              for ($day = 1; $day < 8; $day++) {
                     $dailyMenu = $this->generateDailyMenu($day, $targetCalories, $userId, $recipes);
                     $weeklyMenu = array_merge($weeklyMenu, $dailyMenu->toArray());
              }

              return $weeklyMenu;
       }

       private function generateDailyMenu(int $day, float $targetCalories, int $userId, $recipes)
       {
              $dailyMenu = collect(); // сюда добавляем результат

              foreach ($this->mealTimes as $time) {
                     $timeCalories = $targetCalories * $this->mealPercents[$time];

                     $recipe1 = $recipes->where('meal_type_id', $time)->shuffle()->first();

                     $additionalGrams1 = $this->calculateAdditionalGrams($recipe1, $timeCalories);
                     $recipe1Calories = $recipe1->nutrients['calories'] * ($additionalGrams1 / 100);

                     $dailyMenu->push([
                            'user_id' => $userId,
                            'recipe_id' => $recipe1->id,
                            'additional_grams' => $additionalGrams1,
                            'time' => $time,
                            'day' => $day,
                            'created_at' => now(),
                            'updated_at' => now(),
                     ]);

                     if ($recipe1Calories < $timeCalories * 0.9) {
                            $snack = $recipes->where('meal_type_id', MealTypeEnum::SNACK->value)->shuffle()->first();

                            $additionalGrams2 = $this->calculateAdditionalGrams($snack, $timeCalories - $recipe1Calories);

                            $dailyMenu->push([
                                   'user_id' => $userId,
                                   'recipe_id' => $snack->id,
                                   'additional_grams' => $additionalGrams2,
                                   'time' => $time,
                                   'day' => $day,
                                   'created_at' => now(),
                                   'updated_at' => now(),
                            ]);
                     }
              }

              return $dailyMenu;
       }


       private function calculateAdditionalGrams($recipe, $targetCalories) {
              $grams = ($targetCalories / $recipe->nutrients['calories']) * 100;
              return min($grams, 300);
       }
}