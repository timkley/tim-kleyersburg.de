<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grind_body_measurements', function (Blueprint $table): void {
            $table->id();
            $table->date('date')->unique();
            $table->decimal('weight', 5, 2);
            $table->decimal('body_fat', 4, 1)->nullable();
            $table->decimal('muscle_mass', 4, 1)->nullable();
            $table->unsignedInteger('visceral_fat')->nullable();
            $table->decimal('bmi', 4, 1)->nullable();
            $table->decimal('body_water', 4, 1)->nullable();
            $table->timestamps();
        });
    }
};
