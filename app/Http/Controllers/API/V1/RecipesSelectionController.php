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

                $recipesWithScores = $recipesByTime->map(function($recipe) use ($targetCalories, $targetProtein, $targetFat, $targetCarbs) {
                    $nutrients = $recipe->nutrients;
                    $score = abs(($nutrients['calories'] - $targetCalories) / $targetCalories)
                        + abs(($nutrients['protein'] - $targetProtein) / $targetProtein)
                        + abs(($nutrients['fat'] - $targetFat) / $targetFat)
                        + abs(($nutrients['carbohydrates'] - $targetCarbs) / $targetCarbs);
                    return ['recipe' => $recipe, 'score' => $score];
                });

                $sorted = $recipesWithScores->sortBy('score')->values();
                $topN = $sorted->take(3);
                $chosen = $topN->random();
                $primary = $chosen['recipe'];
                $primaryNutrients = $primary->nutrients;

                // Проверка покрытия нутриентов
                $coverage = (
                    ($primaryNutrients['calories'] / $targetCalories) +
                    ($primaryNutrients['protein'] / $targetProtein) +
                    ($primaryNutrients['fat'] / $targetFat) +
                    ($primaryNutrients['carbohydrates'] / $targetCarbs)
                ) / 4;

                $recipeIds = [$primary->id];

                // Если покрытие меньше 85%, подбираем второе блюдо
                if ($coverage < 0.85) {
                    $additional = $recipesByTime->filter(fn($r) => $r->id !== $primary->id)
                        ->map(function ($r) use ($primaryNutrients, $targetCalories, $targetProtein, $targetFat, $targetCarbs) {
                            $nutrients = $r->nutrients;
                            $combined = [
                                'calories' => $nutrients['calories'] + $primaryNutrients['calories'],
                                'protein' => $nutrients['protein'] + $primaryNutrients['protein'],
                                'fat' => $nutrients['fat'] + $primaryNutrients['fat'],
                                'carbohydrates' => $nutrients['carbohydrates'] + $primaryNutrients['carbohydrates'],
                            ];
                            $score = abs(($combined['calories'] - $targetCalories) / $targetCalories)
                                + abs(($combined['protein'] - $targetProtein) / $targetProtein)
                                + abs(($combined['fat'] - $targetFat) / $targetFat)
                                + abs(($combined['carbohydrates'] - $targetCarbs) / $targetCarbs);
                            return ['recipe' => $r, 'score' => $score];
                        })
                        ->sortBy('score')
                        ->first();

                    if ($additional) {
                        $recipeIds[] = $additional['recipe']->id;
                    }
                }

                foreach ($recipeIds as $recipeId) {
                    $weeklyMenu[] = [
                        'user_id' => $userId,
                        'recipe_id' => $recipeId,
                        'day' => $day,
                        'time' => $time,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Сохраняем в БД
        DB::table('meal_plan')->where('user_id', $userId)->delete();
        DB::table('meal_plan')->insert($weeklyMenu);

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

        return response()->json([
            'message' => 'Меню на неделю успешно сгенерировано',
            'week' => $groupedByDay,
        ]);
    }
}
