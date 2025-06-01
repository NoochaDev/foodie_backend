<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealPlan extends Model
{
       protected $table = 'meal_plan'; // указание таблицы вручную
       public $timestamps = false; // если нет created_at и updated_at

       protected $fillable = [
              'user_id',
              'recipe_id',
              'time',
              'day',
       ];

       public function user()
       {
              return $this->belongsTo(User::class);
       }
       
       public function recipe()
       {
              return $this->belongsTo(Recipe::class);
       }

       public function ingredientReplacements()
       {
              return $this->hasMany(MealPlanIngredientReplacement::class);
       }
}
