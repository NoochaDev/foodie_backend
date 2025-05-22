<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Recipe;
use App\Models\Ingredient;
use App\Models\MealType;

use App\Services\MenuPlanner;

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

        $planner = new MenuPlanner();
        $weeklyMenu = $planner->generateWeeklyMenu($user, $selectedIngredientsIds);

        // Сохраняем в БД
        DB::table('meal_plan')->where('user_id', $userId)->delete();
        DB::table('meal_plan')->insert($weeklyMenu);

        $usedRecipeIds = collect($weeklyMenu)->pluck('recipe_id')->unique();
        $recipes = Recipe::whereIn('id', $usedRecipeIds)->get()->keyBy('id');

        $groupedByDay = $planner->getPlan($weeklyMenu);

        return response()->json([
            'message' => 'Меню на неделю успешно сгенерировано',
            'week' => $groupedByDay,
        ]);
    }
}
