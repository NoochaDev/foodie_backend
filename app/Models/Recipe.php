<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Ingredient;
use App\Models\MealType;

use App\Models\User;

/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $meal_type_id
 * @property string $name
 * @property string $description
 * @property-read mixed $nutrients
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Ingredient> $ingredients
 * @property-read int|null $ingredients_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe whereMealTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recipe whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
}
