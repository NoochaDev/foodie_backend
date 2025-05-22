<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class MealTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mealTypes = [
            ['name' => 'Завтрак'],
            ['name' => 'Обед'],            
            ['name' => 'Ужин'],
        ];

        foreach ($mealTypes as $mealType) {
            DB::table('meal_types')->insert([
                'name' => $mealType['name'],
            ]);
        }
    }
}
