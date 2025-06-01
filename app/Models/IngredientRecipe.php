<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Recipe;
use App\Models\Ingredient;


class IngredientRecipe extends Model
{
    protected $table = 'ingredient_recipe';

    protected $fillable = [
        'recipe_id',
        'ingredient_id',
        'amount'
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    // public function alternatives()
    // {
    //     return $this->hasMany(IngredientReplacement::class, 'original_ingredient_id')
    //                 ->where('recipe_id', $this->recipe_id);
    // }
}
