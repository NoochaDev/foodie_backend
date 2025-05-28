<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use App\Http\Resources\MenuRecipeResource;

use App\Models\User;
use App\Models\Recipe;

use App\Services\MenuPlanning\MenuPlanner;
use App\Services\MenuPlanning\RecipeFilterService;
use App\Services\MenuPlanning\MenuFormatterService;


class RecipesSelectionController extends Controller
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function selectRecipes(Request $request) {
        $user = User::findOrFail(1);
        $userId = $user->id;
        $selectedIngredientsIds = $request->input('selected_ingredients_ids', []);
        $bannedIngredientsIds = $request->input('banned_ingredients_ids', []);

        // Фильтруем рецепты по поступившим из запроса ингредиентам
        $filteredRecipes = app(RecipeFilterService::class)->getFilteredRecipes(
            $selectedIngredientsIds, $bannedIngredientsIds
        );

        // Инициализация планировщика меню на неделю
        $planner = app(MenuPlanner::class);

        $amount_per_day = $user->amount_per_day;

        // Генерируем меню на неделю
        $weeklyMenu = $planner->generateWeeklyMenu(
            $amount_per_day,
            $userId,
            $filteredRecipes,
        );

        // Сохраняем в БД
        DB::table('meal_plan')->where('user_id', $userId)->delete();
        DB::table('meal_plan')->insert($weeklyMenu);

        return new MenuRecipeResource($weeklyMenu);
    }
}
