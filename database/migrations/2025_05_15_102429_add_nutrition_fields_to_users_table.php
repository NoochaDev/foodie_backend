<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->float('protein_amount')->nullable();
            $table->float('fat_amount')->nullable();
            $table->float('carbohydrates_amount')->nullable();
            $table->float('amount_per_day')->nullable(); // TDEE (в г.)
            $table->float('height')->nullable();
            $table->float('weight')->nullable();
            $table->float('age')->nullable();
            $table->string('sex')->nullable();
            $table->string('activity')->nullable();
            $table->string('way')->nullable(); // цель: похудеть, набрать массу и т.д.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'protein_amount',
                'fat_amount',
                'carbohydrates_amount',
                'amount_per_day',
                'height',
                'weight',
                'age',
                'sex',
                'activity',
                'way',
            ]);
        });
    }
};
