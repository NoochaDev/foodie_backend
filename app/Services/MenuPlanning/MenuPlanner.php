<?php

namespace App\Services\MenuPlanning;

use App\Services\MenuPlanning\MealScorerService;
use App\Services\MenuPlanning\RecipeFilterService;
use App\Services\MenuPlanning\MenuFormatterService;

use App\Enums\MealType as MealTypeEnum;

use App\Models\Recipe;
use App\Models\User;
use App\Models\MealType;

use Illuminate\Support\Facades\DB;

class MenuPlanner {
       protected MenuScorerService $calculateService;
       protected RecipeFilterService $filterService;
       // protected MenuFormatterService $menuFormatterService;

       public function __construct(
                     MenuScorerService $calculateService,
                     RecipeFilterService $filterService,
                     // MenuFormatterService $menuFormatterService
              ) {
              $this->calculateService = $calculateService;
              $this->filterService = $filterService;
              // $this->menuFormatterService = $menuFormatterService;
       }

       public function generateWeeklyMenu(User $user, array $selectedIngredientsIds) {
              $filteredRecipes = $this->filterService->getFilteredRecipes($selectedIngredientsIds);

              $times = MealType::pluck('id')->toArray();
              $days = range(1, 7);

              $mealPercents = [
                     MealTypeEnum::BREAKFAST->value => 0.3, 
                     MealTypeEnum::LUNCH->value => 0.4,
                     MealTypeEnum::DINNER->value => 0.3,
              ];

              $weeklyMenu = [];

              foreach ($days as $day) {
                     foreach ($times as $time) {
                            $targetCalories = $user->amount_per_day * $mealPercents[$time];
                            $targetProtein = $user->protein_amount * $mealPercents[$time];
                            $targetFat = $user->fat_amount * $mealPercents[$time];
                            $targetCarbs = $user->carbohydrates_amount * $mealPercents[$time];

                            $recipesByTime = $filteredRecipes->where('meal_type_id', $time);

                            if ($recipesByTime->isEmpty()) {
                                   continue;
                            }

                            $weeklyMenuPart = $this->calculateService->calculateScore(
                                   $recipesByTime, 
                                   $targetCalories, 
                                   $targetProtein, 
                                   $targetFat, $targetCalories, 
                                   $user->id, $day, $time
                            );

                            $weeklyMenu = array_merge($weeklyMenu, $weeklyMenuPart);
                     }
              }

              return $weeklyMenu;
       }
}
