<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Recipe;

/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MealType extends Model
{
    protected $fillable = [
        'name'
    ];

    public function recipes() {
        $this->hasMany(Recipe::class);
    }
}
