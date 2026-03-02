<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grind_meals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('nutrition_day_id')->constrained('grind_nutrition_days')->cascadeOnDelete();
            $table->string('name');
            $table->string('time')->nullable();
            $table->unsignedInteger('kcal');
            $table->unsignedInteger('protein');
            $table->unsignedInteger('fat');
            $table->unsignedInteger('carbs');
            $table->timestamps();
        });
    }
};
