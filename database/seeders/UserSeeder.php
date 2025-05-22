<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
            'name' => 'Egor',
            'email' => 'egor@gmail.com',
            'password' => bcrypt('password'),
            'height' => 160,
            'weight' => 110,
            'age' => 20,
            'sex' => 'male',
            'activity' => 'low',
            'way' => 'lose_weight',],
        ];

        foreach ($users as $user) {
            DB::table('users')->insert([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => $user['password'],
                'height' => $user['height'],
                'weight' => $user['weight'],
                'age' => $user['age'],
                'sex' => $user['sex'],
                'activity' => $user['activity'],
                'way' => $user['way'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
