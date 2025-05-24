<?php

namespace App\Services\MenuPlanning;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;

use App\Services\MenuPlanning\RecipeSelectorService;


class MenuPlanner {
       protected RecipeSelectorService $recipeSelectorService;

       public function __construct(
                     RecipeSelectorService $recipeSelectorService,
              ) {
              $this->recipeSelectorService = $recipeSelectorService;
       }

       /**
        * @param int $userId
        * @param Collection|array $filteredRecipes Коллекция рецептов (уже отфильтрованных)
        * @param array $mealTimes Массив ID приемов пищи, например [1,2,3]
        * @param array $mealPercents Проценты распределения по приемам пищи, например [1 => 0.3, 2 => 0.4, 3 => 0.3]
        * @return array Массив с меню на неделю
        */
       public function generateWeeklyMenu(int $userId, float $amount_per_day, float $protein_amount, float $fat_amount, 
       float $carbohydrates_amount, EloquentCollection|array $filteredRecipes, array $mealPercents, 
       array $mealTimes) : array 
       {
              $days = range(1, 7);
              $weeklyMenu = [];

              foreach ($days as $day) {
                     foreach ($mealTimes as $time) {
                            $targetCalories = $amount_per_day * $mealPercents[$time];
                            $targetProtein = $protein_amount * $mealPercents[$time];
                            $targetFat = $fat_amount * $mealPercents[$time];
                            $targetCarbs = $carbohydrates_amount * $mealPercents[$time];

                            if (is_array($filteredRecipes)) {
                                   $filteredRecipes = collect($filteredRecipes);
                            }

                            $recipesByTime = $filteredRecipes->where('meal_type_id', $time);

                            if ($recipesByTime->isEmpty()) {
                                   continue;
                            }

                            $weeklyMenuPart = $this->recipeSelectorService->selectMealForTimeSlot(
                                   $recipesByTime, 
                                   $targetCalories, 
                                   $targetProtein, 
                                   $targetFat, $targetCarbs, 
                                   $userId, $day, $time
                            );

                            $weeklyMenu = array_merge($weeklyMenu, $weeklyMenuPart);
                     }
              }

              return $weeklyMenu;
       }
}
