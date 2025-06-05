<?php

namespace App\Services\MenuPlanning;

use Illuminate\Support\Collection;

use App\Enums\MealType as MealTypeEnum;


class MenuPlanner {
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

       protected float $targetCalories;
       protected int $userId;
       protected $recipes;

       public function __construct(float $targetCalories, int $userId, $recipes)  {
              $this->targetCalories = $targetCalories;
              $this->userId = $userId;
              $this->recipes = collect($recipes);
       }
       
       /** 
        * Генерирует меню на неделю с учетом целевой калорийности и рецептов которые подходят пользователю
        * @param float $targetCalories Целевая калорийность на день
        * @param int $userId ID пользователя
        * @param \Illuminate\Support\Collection|array $recipes Коллекция или массив рецептов
        * @return array Сформированное меню на неделю
       */
       public function generateWeeklyMenu() : array {
              $weeklyMenu = [];

              for ($day = 1; $day < 8; $day++) {
                     $dailyMenu = $this->generateDailyMenu($day, $this->targetCalories, $this->userId, $this->recipes);
                     $weeklyMenu = array_merge($weeklyMenu, $dailyMenu->toArray());
              }

              return $weeklyMenu;
       }


       /**
        * Генерирует меню на один день с учётом целевой калорийности.
        *
        * @param int $day День недели (0–6)
        * @param float $targetCalories Целевая калорийность на день
        * @param int $userId ID пользователя
        * @param \Illuminate\Support\Collection|array $recipes Коллекция или массив рецептов
        * @return \Illuminate\Support\Collection Сформированное меню за день
       */
       private function generateDailyMenu(int $day, float $targetCalories, int $userId, $recipes) : Collection
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

       /**
        * Расчитывает количество дополнительных граммов для рецепта
        * @param \App\Models\Recipe $recipe Рецепт
        * @param float $targetCalories Целевая калорийность
        * @return int Количество дополнительных граммов (не более 300)
        */
       private function calculateAdditionalGrams($recipe, float $targetCalories) : int {
              $grams = ($targetCalories / $recipe->nutrients['calories']) * 100;
              return min($grams, 300);
       }
}