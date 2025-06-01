<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class IngredientReplacement extends Model
{
    protected $fillable = [
        'recipe_id',
        'original_ingredient_id',
        'alternative_ingredient_id',
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function originalIngredient()
    {
        return $this->belongsTo(Ingredient::class, 'original_ingredient_id');
    }

    public function alternativeIngredient()
    {
        return $this->belongsTo(Ingredient::class, 'alternative_ingredient_id');
    }
}
