<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $recipes = [
            // Завтраки
            ['meal_type_id' => 1, 'name' => 'Овсянка с молоком', 'description' => 'Питательный завтрак'],
            ['meal_type_id' => 1, 'name' => 'Яичница', 'description' => 'Жареные яйца'],
            ['meal_type_id' => 1, 'name' => 'Творог с бананом', 'description' => 'Завтрак с творогом'],
            ['meal_type_id' => 1, 'name' => 'Блины с мёдом', 'description' => 'Сладкий завтрак'],
            ['meal_type_id' => 1, 'name' => 'Йогурт с гранолой', 'description' => 'Полезный завтрак'],
            ['meal_type_id' => 1, 'name' => 'Смузи из банана и шпината', 'description' => 'Витаминный заряд с утра'],
            ['meal_type_id' => 1, 'name' => 'Тост с авокадо', 'description' => 'Легкий и питательный завтрак'],
            ['meal_type_id' => 1, 'name' => 'Омлет с овощами', 'description' => 'Быстрый и вкусный завтрак'],
            ['meal_type_id' => 1, 'name' => 'Панкейки с ягодами', 'description' => 'Сладкий и сытный завтрак'],
            ['meal_type_id' => 1, 'name' => 'Каша из гречки с молоком', 'description' => 'Полезный завтрак'],

            // Обеды
            ['meal_type_id' => 2, 'name' => 'Куриное филе с гарниром', 'description' => 'Филе с гарниром'],
            ['meal_type_id' => 2, 'name' => 'Гречка с яйцом', 'description' => 'Простое и сытное блюдо'],
            ['meal_type_id' => 2, 'name' => 'Тунец с рисом', 'description' => 'Полезный обед'],
            ['meal_type_id' => 2, 'name' => 'Суп из чечевицы', 'description' => 'Питательный суп'],
            ['meal_type_id' => 2, 'name' => 'Рагу из овощей с говядиной', 'description' => 'Сытный обед'],
            ['meal_type_id' => 2, 'name' => 'Запечённая свинина с картофелем', 'description' => 'Классическое блюдо'],
            ['meal_type_id' => 2, 'name' => 'Салат с фасолью и овощами', 'description' => 'Лёгкий и питательный обед'],
            ['meal_type_id' => 2, 'name' => 'Паста с курицей и брокколи', 'description' => 'Итальянский обед'],
            ['meal_type_id' => 2, 'name' => 'Рыба на пару с овощами', 'description' => 'Полезный обед'],
            ['meal_type_id' => 2, 'name' => 'Картофельное пюре с куриной котлетой', 'description' => 'Сытное блюдо'],
            ['meal_type_id' => 2, 'name' => 'Салат Цезарь с курицей', 'description' => 'Популярный салат'],
            ['meal_type_id' => 2, 'name' => 'Тушеная капуста с мясом', 'description' => 'Домашний обед'],

            // Ужины
            ['meal_type_id' => 3, 'name' => 'Салат овощной', 'description' => 'Легкий овощной салат'],
            ['meal_type_id' => 3, 'name' => 'Макароны с сыром', 'description' => 'Ужин с углеводами'],
            ['meal_type_id' => 3, 'name' => 'Йогурт с фруктами', 'description' => 'Легкий ужин'],
            ['meal_type_id' => 3, 'name' => 'Омлет с овощами', 'description' => 'Быстрый ужин'],
            ['meal_type_id' => 3, 'name' => 'Запечённый лосось с овощами', 'description' => 'Питательный ужин'],
            ['meal_type_id' => 3, 'name' => 'Творог с мёдом и орехами', 'description' => 'Полезный перекус'],
            ['meal_type_id' => 3, 'name' => 'Суп-пюре из брокколи', 'description' => 'Лёгкий вечерний суп'],
            ['meal_type_id' => 3, 'name' => 'Овощное рагу', 'description' => 'Вкусный и лёгкий ужин'],
            ['meal_type_id' => 3, 'name' => 'Греческий салат', 'description' => 'Свежий и лёгкий ужин'],
            ['meal_type_id' => 3, 'name' => 'Запечённая курица с овощами', 'description' => 'Сытный ужин'],
            ['meal_type_id' => 3, 'name' => 'Каша из киноа с овощами', 'description' => 'Полезный ужин'],
            ['meal_type_id' => 3, 'name' => 'Салат с тунцом и яйцом', 'description' => 'Белковый ужин'],
        ];

        foreach ($recipes as $recipe) {
            DB::table('recipes')->insert([
                'meal_type_id' => $recipe['meal_type_id'],
                'name' => $recipe['name'],
                'description' => $recipe['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
