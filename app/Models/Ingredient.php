<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Recipe;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'protein',
        'carbohydrates',
        'fat',
    ];

    public function recipes() {
        return $this->belongsToMany(Recipe::class)
        ->withPivot('amount')
        ->withTimestamps();;
    }
}
