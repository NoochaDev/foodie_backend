<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enums\MealType as MealTypeEnum;

use App\Models\IngredientReplacement;
use App\Models\Ingredient;
use App\Models\MealType;

use App\Models\User;

class Recipe extends Model
{
    protected $fillable = [
        'meal_type_id',
        'name',
        'description',
    ];

    protected $casts = [
        'meal_type_id' => MealTypeEnum::class,
    ];

    public function users() {
        return $this->belongsToMany(User::class, 'meal_plan')
            ->withPivot('day', 'time');
    }

    public function mealType() {
        $this->belongsTo(MealType::class);
    }

    public function ingredients() {
        return $this->belongsToMany(Ingredient::class)
            ->withPivot('amount')
            ->withTimestamps();
    }

    public function ingredientReplacements() {
        return $this->hasMany(IngredientReplacement::class);
    }

    
    /**
     * Возвращает нутриенты исходя из своих ингредиентов (аксессор)
     */
    public function getNutrientsAttribute()
    {
        $protein = $fat = $carbohydrates = 0;

        foreach ($this->ingredients as $ingredient) {
            $amount = $ingredient->pivot->amount;

            $protein += $ingredient->protein * $amount / 100;
            $fat     += $ingredient->fat * $amount / 100;
            $carbohydrates   += $ingredient->carbohydrates * $amount / 100;
        }

        $calories = 4 * $protein + 9 * $fat + 4 * $carbohydrates;

        return compact('protein', 'fat', 'carbohydrates', 'calories');
    }

    /**
     * Возвращает ингредиенты с альтернативами (аксессор)
     */
    public function getIngredientsWithAlternatives()
    {
        return collect($this->ingredients->map(function (Ingredient $ingredient) {
            // Берем альтернативы из метода ingredientReplacements
            $alternatives = $this->ingredientReplacements
            ->where('original_ingredient_id', $ingredient->id)
            ->map(function (IngredientReplacement $replacement) {
                return [
                    'id' => $replacement->alternativeIngredient->id,
                    'name' => $replacement->alternativeIngredient->name,
                    'protein' => $replacement->alternativeIngredient->protein,
                    'fat' => $replacement->alternativeIngredient->fat,
                    'carbohydrates' => $replacement->alternativeIngredient->carbohydrates,
                ];
            });

            // Возвращаем все ингредиенты (парами: оригинальные и альтернативные ингредиенты)
            return [
                'original' => [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'protein' => $ingredient->protein,
                    'fat' => $ingredient->fat,
                    'carbohydrates' => $ingredient->carbohydrates,
                    'alternatives' => $alternatives->values(),
                ],
            ];
        }));
    }
}
