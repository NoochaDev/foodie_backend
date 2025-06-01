<?php

namespace App\Services\MenuPlanning;

use App\Models\MealPlan;
use App\Models\User;
use Illuminate\Support\Collection;


class DayPlan
{
       protected User $user;
       protected int $day;

       public function __construct(User $user, int $day)
       {
              $this->user = $user;
              $this->day = $day;
       }

       public function getRecipes(): Collection
       {
              return MealPlan::with('recipe')
                     ->where('user_id', $this->user->id)
                     ->where('day', $this->day)
                     ->get()
                     ->pluck('recipe');
       }

       public function getDay(): int
       {
              return $this->day;
       }
}