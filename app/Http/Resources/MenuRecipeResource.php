<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Enums\MealType as MealTypeEnum;


class MenuRecipeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $weeklyMenu = $this->resource['weeklyMenu'];
        $recipes = $this->resource['recipes'];

        /**
         * Возвращаем коллекцию
         */
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

            /**
             * Формирование недельного меню на уровне "завтрак, обед, ужин"
             */
            foreach ($dayEntries as $entry) {
                $mealEnum = MealTypeEnum::tryFrom($entry['time']);
                if (!$mealEnum) continue;

                $mealKey = $mealEnum->label(); // 'breakfast', 'lunch', и т.д.

                $recipe = $recipes[$entry['recipe_id']] ?? null;
                if (!$recipe) continue;

                $recipe = $recipes[$entry['recipe_id']];
                $nutrients = $recipe->nutrients;

                // Добавляем рецепт в соответствующий прием пищи вместе с его ингредиентами
                $dayResult[$mealKey][] = [
                    'id' => $recipe->id,
                    'title' => $recipe->title,
                    'meal_type_id' => $recipe->meal_type_id,
                    'ingredients' => $recipe->getIngredientsWithAlternatives(),
                    'nutrients' => $nutrients,
                ];

                /**
                 * Нутриенты с учетом $scale
                 */
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
