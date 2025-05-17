<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Recipe;

class MealType extends Model
{
    protected $fillable = [
        'name'
    ];

    public function recipes() {
        $this->hasMany(Recipe::class);
    }
}
