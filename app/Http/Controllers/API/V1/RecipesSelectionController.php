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
        $userId = User::findOrFail(1)->id;
        $selectedIngredientsIds = $request->input('selected_ingredients_ids', []);

        $recipes = Recipe::with('ingredients')->get();

        // 1. Берем те рецепты, у которых отсутствует МАКСИМУМ 2 ингредиента 
        $filteredRecipes = $recipes->filter(function($recipe) use ($selectedIngredientsIds) {
            $recipeIngredientIds = $recipe->ingredients->pluck('id')->toArray();
            $missingCount = count(array_diff($recipeIngredientIds, $selectedIngredientsIds));

            return $missingCount <= 2;
        });

        // 2. Формирование меню, цикл по дням недели + завтрак, обед, ужин
        $times = MealType::pluck('id')->toArray();
        $days = range(1, 7);
        
        $weeklyMenu = [];

        foreach ($days as $day) {
            foreach ($times as $time) {
                $recipesByTime = $filteredRecipes->where('meal_type_id', $time);
                
                if ($recipesByTime->isEmpty()) {
                    continue;
                }

                $randomRecipe = $recipesByTime->random();

                $weeklyMenu[] = [
                    'user_id' => $userId,
                    'recipe_id' => $randomRecipe->id,
                    'day' => $day,
                    'time' => $time, // сохраняем id типа
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // 3. Сохраняем в meal_plan
        DB::table('meal_plan')->where('user_id', $userId)->delete();
        DB::table('meal_plan')->insert($weeklyMenu);

        // 4. Возвращаем респонс
        return response()->json([
            'message' => 'Меню на неделю успешно сгенерировано',
            'data' => $weeklyMenu,
        ]);
    }

    // public function selectRecipes(Request $request) {
    //     $data = $request->validate([
    //         'ingredients_ids' => 'required|array',
    //         'ingredients_ids.*' => 'integer|exists:ingredients,id'
    //     ]);
// 
    //     // Для примера — жестко заданные цели по БЖУ и калориям (нужно заменить на данные текущего пользователя)
    //     $kcalTarget = 2039 / 3;
    //     $proteinTarget = 204 / 3;
    //     $fatTarget = 68 / 3;
    //     $carbsTarget = 153 / 3;
// 
    //     // Погрешности (в процентах, например 0.1 = 10%)
    //     $kcalTolerance = 0.05;    // ±5%
    //     $proteinTolerance = 0.1;  // ±10%
    //     $fatTolerance = 0.1;      // ±10%
    //     $carbsTolerance = 0.1;    // ±10%
// 
    //     $ingredientsIds = $data['ingredients_ids'];
// 
    //     // Достаём рецепты, которые содержат все указанные ингредиенты
    //     $recipes = Recipe::whereHas('ingredients', function ($query) use ($ingredientsIds) {
    //         $query->whereIn('ingredients.id', $ingredientsIds);
    //     }, '=', count($ingredientsIds))->with('ingredients')->get();
// 
    //     // Фильтруем рецепты по калориям и БЖУ с учетом количества ингредиентов
    //     $filtered = $recipes->filter(function ($recipe) use (
    //         $kcalTarget, $proteinTarget, $fatTarget, $carbsTarget,
    //         $kcalTolerance, $proteinTolerance, $fatTolerance, $carbsTolerance
    //     ) {
    //         $totals = [
    //             'kcal' => 0,
    //             'protein' => 0,
    //             'fat' => 0,
    //             'carbs' => 0,
    //         ];
// 
    //         foreach ($recipe->ingredients as $ingredient) {
    //             $amountFactor = $ingredient->pivot->amount / 100; // граммы / 100г для расчёта нутриентов
// 
    //             $totals['kcal'] += $amountFactor;
    //             $totals['protein'] += $ingredient->protein;
    //             $totals['fat'] += $ingredient->fat;
    //             $totals['carbs'] += $ingredient->carbohydrates;
    //         }
// 
    //         // Проверяем, что итоговые значения в пределах допустимой погрешности
    //         return
    //             ($totals['kcal'] >= $kcalTarget * (1 - $kcalTolerance) && $totals['kcal'] <= $kcalTarget * (1 + $kcalTolerance)) &&
    //             ($totals['protein'] >= $proteinTarget * (1 - $proteinTolerance) && $totals['protein'] <= $proteinTarget * (1 + $proteinTolerance)) &&
    //             ($totals['fat'] >= $fatTarget * (1 - $fatTolerance) && $totals['fat'] <= $fatTarget * (1 + $fatTolerance)) &&
    //             ($totals['carbs'] >= $carbsTarget * (1 - $carbsTolerance) && $totals['carbs'] <= $carbsTarget * (1 + $carbsTolerance));
    //     });
// 
    //     return response()->json($filtered->values());
    // } 
}
