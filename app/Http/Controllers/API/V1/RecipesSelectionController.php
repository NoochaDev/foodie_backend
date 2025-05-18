<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Recipe;
use App\Models\Ingredient;
use App\Models\MealType;

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

        // $recipes = Recipe::with('ingredients')->get();
// 
        // $filteredRecipes = $recipes->filter(function($recipe) use ($selectedIngredientsIds) {
        //     $recipeIngredientIds = $recipe->ingredients->pluck('id')->toArray();
        //     $missingCount = count(array_diff($recipeIngredientIds, $selectedIngredientsIds));
        //     return $missingCount <= 2;
        // });

        $filteredRecipes = Recipe::select('recipes.*')
            ->join('ingredient_recipe', 'recipes.id', '=', 'ingredient_recipe.recipe_id')
            ->leftJoin('ingredients', 'ingredients.id', '=', 'ingredient_recipe.ingredient_id')
            ->groupBy('recipes.id')
            ->havingRaw('SUM(CASE WHEN ingredients.id NOT IN (' . implode(',', $selectedIngredientsIds ?: [0]) . ') THEN 1 ELSE 0 END) <= 2')
            ->with('ingredients')
            ->get();

        $times = MealType::pluck('id')->toArray();
        $days = range(1, 7);

        $mealPercents = [
            1 => 0.3, // завтрак
            2 => 0.4, // обед
            3 => 0.3, // ужин
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

                $bestRecipe = null;
                $bestScore = PHP_INT_MAX;
                
                // Вычисляем score для каждого рецепта
                $recipesWithScores = $recipesByTime->map(function($recipe) use ($targetCalories, $targetProtein, $targetFat, $targetCarbs) {
                    $nutrients = $recipe->nutrients;
                    $score = abs(($nutrients['calories'] - $targetCalories) / $targetCalories)
                        + abs(($nutrients['protein'] - $targetProtein) / $targetProtein)
                        + abs(($nutrients['fat'] - $targetFat) / $targetFat)
                        + abs(($nutrients['carbohydrates'] - $targetCarbs) / $targetCarbs);
                    return ['recipe' => $recipe, 'score' => $score];
                });

                // Сортируем по score (возрастание)
                $sorted = $recipesWithScores->sortBy('score')->values();

                // Берем топ-3 (или меньше, если их меньше)
                $topN = $sorted->take(3);

                // Случайно выбираем один из топ-3
                $chosen = $topN->random();

                $bestRecipe = $chosen['recipe'];

                $weeklyMenu[] = [
                    'user_id' => $userId,
                    'recipe_id' => $bestRecipe->id,
                    'day' => $day,
                    'time' => $time,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Очистка и запись в БД
        DB::table('meal_plan')->where('user_id', $userId)->delete();
        DB::table('meal_plan')->insert($weeklyMenu);

        return response()->json([
            'message' => 'Меню на неделю успешно сгенерировано',
            'data' => $weeklyMenu,
        ]);
    }
}
