<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Enums\MealType as MealTypeEnum;

use App\Models\IngredientReplacement;
use App\Models\Ingredient;
use App\Models\Recipe;


class MenuRecipeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $weeklyMenu = $this->resource;

        $usedRecipeIds = collect($weeklyMenu)->pluck('recipe_id')->unique();
        $recipes = Recipe::whereIn('id', $usedRecipeIds)->get()->keyBy('id');

        return collect($weeklyMenu)->groupBy('day')->map(function ($dayEntries) use ($recipes) {
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
                'additional_grams' => 0,
            ];

            foreach ($dayEntries as $entry) {
                $mealEnum = MealTypeEnum::tryFrom($entry['time']);
                if (!$mealEnum) continue;

                $mealKey = $mealEnum->label(); // 'breakfast', 'lunch', и т.д.

                $recipe = $recipes[$entry['recipe_id']] ?? null;
                if (!$recipe) continue;

                $recipe = $recipes[$entry['recipe_id']];
                $nutrients = $recipe->nutrients;

                $dayResult[$mealKey][] = [
                    'id' => $recipe->id,
                    'title' => $recipe->title,
                    'meal_type_id' => $recipe->meal_type_id,
                    'ingredients' => $recipe->ingredients->map(function (Ingredient $ingredient) use ($recipe) {
                        $alternatives = $recipe->ingredientReplacements->map(function (IngredientReplacement $replacement) use ($recipe) {
                            return [
                                'id' => $replacement->alternativeIngredient->id,
                                'name' => $replacement->alternativeIngredient->name,
                                'protein' => $replacement->alternativeIngredient->protein,
                                'fat' => $replacement->alternativeIngredient->fat,
                                'carbohydrates' => $replacement->alternativeIngredient->carbohydrates,
                            ];
                        });

                        return [
                            'original' => [
                                'id' => $ingredient->id,
                                'name' => $ingredient->name,
                                'protein' => $ingredient->protein,
                                'fat' => $ingredient->fat,
                                'carbohydrates' => $ingredient->carbohydrates,
                            ],
                            'alternatives' => $alternatives->values(), // может быть пустым, если альтернатив нет
                        ];
                    }),
                    'nutrients' => $nutrients,
                ];

                $scale = $entry['additional_grams'] / 100;

                $dayResult['totals']['calories'] += ($nutrients['calories'] ?? 0) * $scale;
                $dayResult['totals']['protein'] += ($nutrients['protein'] ?? 0) * $scale;
                $dayResult['totals']['fat'] += ($nutrients['fat'] ?? 0) * $scale;
                $dayResult['totals']['carbohydrates'] += ($nutrients['carbohydrates'] ?? 0) * $scale;

                $dayResult['additional_grams'] += $entry['additional_grams'];
            }

            return $dayResult;
        });
    }
}
