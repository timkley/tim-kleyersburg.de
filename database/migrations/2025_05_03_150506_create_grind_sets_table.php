<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grind_sets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('workout_id');
            $table->unsignedInteger('exercise_id');
            $table->unsignedInteger('reps');
            $table->float('weight');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->timestamps();
        });
    }
};
