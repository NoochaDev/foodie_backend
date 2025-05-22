<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ingredients = [
            ['name' => 'Яйцо', 'protein' => 13, 'carbohydrates' => 1, 'fat' => 11],
            ['name' => 'Овсянка', 'protein' => 12, 'carbohydrates' => 60, 'fat' => 7],
            ['name' => 'Молоко', 'protein' => 3, 'carbohydrates' => 5, 'fat' => 3],
            ['name' => 'Куриное филе', 'protein' => 23, 'carbohydrates' => 0, 'fat' => 2],
            ['name' => 'Рис', 'protein' => 2.7, 'carbohydrates' => 28, 'fat' => 0.3],
            ['name' => 'Гречка', 'protein' => 13, 'carbohydrates' => 72, 'fat' => 3.4],
            ['name' => 'Банан', 'protein' => 1.1, 'carbohydrates' => 23, 'fat' => 0.3],
            ['name' => 'Яблоко', 'protein' => 0.3, 'carbohydrates' => 14, 'fat' => 0.2],
            ['name' => 'Миндаль', 'protein' => 21, 'carbohydrates' => 22, 'fat' => 49],
            ['name' => 'Говядина', 'protein' => 26, 'carbohydrates' => 0, 'fat' => 15],
            ['name' => 'Свинина', 'protein' => 25, 'carbohydrates' => 0, 'fat' => 20],
            ['name' => 'Картофель', 'protein' => 2, 'carbohydrates' => 17, 'fat' => 0.1],
            ['name' => 'Морковь', 'protein' => 0.9, 'carbohydrates' => 10, 'fat' => 0.2],
            ['name' => 'Лук', 'protein' => 1.1, 'carbohydrates' => 9, 'fat' => 0.1],
            ['name' => 'Чеснок', 'protein' => 6.4, 'carbohydrates' => 33, 'fat' => 0.5],
            ['name' => 'Помидор', 'protein' => 0.9, 'carbohydrates' => 3.9, 'fat' => 0.2],
            ['name' => 'Огурец', 'protein' => 0.7, 'carbohydrates' => 3.6, 'fat' => 0.1],
            ['name' => 'Сыр', 'protein' => 25, 'carbohydrates' => 1.3, 'fat' => 33],
            ['name' => 'Творог', 'protein' => 18, 'carbohydrates' => 3.3, 'fat' => 9],
            ['name' => 'Йогурт', 'protein' => 10, 'carbohydrates' => 4, 'fat' => 3],
            ['name' => 'Хлеб', 'protein' => 8, 'carbohydrates' => 50, 'fat' => 1],
            ['name' => 'Макароны', 'protein' => 5, 'carbohydrates' => 25, 'fat' => 1],
            ['name' => 'Капуста', 'protein' => 1.3, 'carbohydrates' => 6, 'fat' => 0.1],
            ['name' => 'Брокколи', 'protein' => 2.8, 'carbohydrates' => 7, 'fat' => 0.4],
            ['name' => 'Шпинат', 'protein' => 2.9, 'carbohydrates' => 3.6, 'fat' => 0.4],
            ['name' => 'Кукуруза', 'protein' => 3.2, 'carbohydrates' => 19, 'fat' => 1.5],
            ['name' => 'Горошек', 'protein' => 5, 'carbohydrates' => 14, 'fat' => 0.4],
            ['name' => 'Фасоль', 'protein' => 9, 'carbohydrates' => 27, 'fat' => 0.5],
            ['name' => 'Чечевица', 'protein' => 9, 'carbohydrates' => 20, 'fat' => 0.4],
            ['name' => 'Кефир', 'protein' => 3, 'carbohydrates' => 4, 'fat' => 3],
            ['name' => 'Сметана', 'protein' => 2.5, 'carbohydrates' => 3, 'fat' => 20],

            // Новые добавленные ингредиенты:
            ['name' => 'Куриные яйца (варёные)', 'protein' => 13, 'carbohydrates' => 1.1, 'fat' => 11],
            ['name' => 'Лосось', 'protein' => 20, 'carbohydrates' => 0, 'fat' => 13],
            ['name' => 'Тунец', 'protein' => 23, 'carbohydrates' => 0, 'fat' => 1],
            ['name' => 'Индейка', 'protein' => 29, 'carbohydrates' => 0, 'fat' => 2],
            ['name' => 'Грецкий орех', 'protein' => 15, 'carbohydrates' => 14, 'fat' => 65],
            ['name' => 'Авокадо', 'protein' => 2, 'carbohydrates' => 9, 'fat' => 15],
            ['name' => 'Киноа', 'protein' => 14, 'carbohydrates' => 64, 'fat' => 6],
            ['name' => 'Свекла', 'protein' => 1.6, 'carbohydrates' => 10, 'fat' => 0.2],
            ['name' => 'Морская капуста', 'protein' => 1, 'carbohydrates' => 5, 'fat' => 0.5],
            ['name' => 'Батат', 'protein' => 2, 'carbohydrates' => 20, 'fat' => 0.1],
            ['name' => 'Оливковое масло', 'protein' => 0, 'carbohydrates' => 0, 'fat' => 100],
            ['name' => 'Мёд', 'protein' => 0.3, 'carbohydrates' => 82, 'fat' => 0],
            ['name' => 'Сахар', 'protein' => 0, 'carbohydrates' => 100, 'fat' => 0],
            ['name' => 'Чёрный чай', 'protein' => 0, 'carbohydrates' => 0, 'fat' => 0],
            ['name' => 'Кофе', 'protein' => 0, 'carbohydrates' => 0, 'fat' => 0],
            ['name' => 'Свежий огурец', 'protein' => 0.6, 'carbohydrates' => 3.6, 'fat' => 0.1],
            ['name' => 'Салат ромэн', 'protein' => 1.2, 'carbohydrates' => 3, 'fat' => 0.3],
            ['name' => 'Томатный соус', 'protein' => 1.2, 'carbohydrates' => 7, 'fat' => 0.1],
            ['name' => 'Грибы шампиньоны', 'protein' => 3.1, 'carbohydrates' => 3.3, 'fat' => 0.3],
            ['name' => 'Кокосовое молоко', 'protein' => 2, 'carbohydrates' => 6, 'fat' => 24],
        ];

        foreach ($ingredients as $ingredient) {
            DB::table('ingredients')->insert([
                'name' => $ingredient['name'],
                'protein' => $ingredient['protein'],
                'carbohydrates' => $ingredient['carbohydrates'],
                'fat' => $ingredient['fat'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
