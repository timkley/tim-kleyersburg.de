<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grind_nutrition_days', function (Blueprint $table): void {
            $table->id();
            $table->date('date')->unique();
            $table->string('type');
            $table->string('training_label')->nullable();
            $table->json('meals');
            $table->text('notes')->nullable();
            $table->unsignedInteger('total_kcal')->default(0);
            $table->unsignedInteger('total_protein')->default(0);
            $table->unsignedInteger('total_fat')->default(0);
            $table->unsignedInteger('total_carbs')->default(0);
            $table->timestamps();
        });
    }
};
