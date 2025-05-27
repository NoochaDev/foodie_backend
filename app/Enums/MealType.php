<?php

namespace App\Enums;

enum MealType: int {
       case BREAKFAST = 1;
       case LUNCH = 2;
       case DINNER = 3;
       case SNACK = 4;

       public function label() : string {
              return match($this) {
                     self::BREAKFAST => 'breakfast',
                     self::LUNCH => 'lunch',
                     self::DINNER => 'dinner',
                     self::SNACK => 'snack',
              };
       }
}
