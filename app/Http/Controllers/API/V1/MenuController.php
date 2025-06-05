<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use App\Http\Resources\MenuRecipeResource;

use App\Models\User;
use App\Models\MealPlan;
use App\Models\Recipe;

use App\Services\MenuPlanning\MenuPlanner;
use App\Services\MenuPlanning\DayPlan;
use App\Services\MenuPlanning\RecipeFilterService;


class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $recipes = Recipe::with('ingredients')->get();
        return response()->json($recipes);
    }

    /**
     * Возвращает меню питания на день
     */
    public function dailyMenu(Request $request) {
        $user = User::findOrFail(1);
        $dailyMenu = new DayPlan($user, $request->integer('day'));

        $mealPlanForDay = MealPlan::with([
            'recipe.ingredients',
            'recipe.ingredientReplacements.alternativeIngredient'
        ])->where('user_id', $user->id)
        ->where('day', $request->integer('day'))
        ->get();

        // Загружаем все рецепты, которые участвуют в меню
        $recipeIds = $mealPlanForDay->pluck('recipe_id')->unique();

        $recipes = Recipe::with([
            'ingredients',
            'ingredientReplacements.alternativeIngredient'
        ])
        ->whereIn('id', $recipeIds)
        ->get()
        ->keyBy('id');

        // Возвращаем через MenuRecipeResource
        return new MenuRecipeResource([
            'weeklyMenu' => $mealPlanForDay,
            'recipes' => $recipes,
        ]);
    }

    /**
     * Генерация меню питания на неделю
     */
    public function selectRecipes(Request $request) {
        $user = User::findOrFail(1);
        $userId = $user->id;
        $amount_per_day = $user->amount_per_day;
        $selectedIngredientsIds = $request->input('selected_ingredients_ids', []);
        $bannedIngredientsIds = $request->input('banned_ingredients_ids', []);

        // Фильтруем рецепты по поступившим из запроса ингредиентам
        $filteredRecipes = app(RecipeFilterService::class)->getFilteredRecipes(
            $selectedIngredientsIds, $bannedIngredientsIds
        );

        // Инициализация планировщика меню на неделю
        $planner = new MenuPlanner(
            $amount_per_day,
            $userId,
            $filteredRecipes,
        );

        // Генерируем меню на неделю
        $weeklyMenu = $planner->generateWeeklyMenu();

        // Сохраняем в БД
        DB::table('meal_plan')->where('user_id', $userId)->delete();
        DB::table('meal_plan')->insert($weeklyMenu);

        // Подготавливаем данные к передаче в ресурс
        $usedRecipeIds = collect($weeklyMenu)->pluck('recipe_id')->unique();
        $recipes = Recipe::with([
            'ingredients',
            'ingredientReplacements.alternativeIngredient'
        ])
        ->whereIn('id', $usedRecipeIds)
        ->get()->keyBy('id');

        // Возвращаем ресурс
        return new MenuRecipeResource([
            'weeklyMenu' => $weeklyMenu, 
            'recipes' => $recipes
        ]);
    }
}
