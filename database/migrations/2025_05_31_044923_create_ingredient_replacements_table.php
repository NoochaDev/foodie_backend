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
        Schema::create('ingredient_replacements', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Added fields
            $table->unsignedBigInteger('recipe_id');
            $table->unsignedBigInteger('original_ingredient_id');
            $table->unsignedBigInteger('alternative_ingredient_id');

            $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('cascade');
            $table->foreign('original_ingredient_id')->references('id')->on('ingredients')->onDelete('cascade');
            $table->foreign('alternative_ingredient_id')->references('id')->on('ingredients')->onDelete('cascade');

            $table->unique(['recipe_id', 'original_ingredient_id', 'alternative_ingredient_id'], 'replacement_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredient_replacements');
    }
};
