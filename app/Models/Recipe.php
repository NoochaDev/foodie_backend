<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function getNutrientsAttribute()
    {
        $protein = $fat = $carbs = 0;

        foreach ($this->ingredients as $ingredient) {
            $amount = $ingredient->pivot->amount;

            $protein += $ingredient->protein * $amount / 100;
            $fat     += $ingredient->fat * $amount / 100;
            $carbs   += $ingredient->carbohydrates * $amount / 100;
        }

        $calories = 4 * $protein + 9 * $fat + 4 * $carbs;

        return compact('protein', 'fat', 'carbohydrates', 'calories');
    }
}
