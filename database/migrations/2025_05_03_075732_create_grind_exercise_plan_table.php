<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grind_exercise_plan', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('exercise_id');
            $table->unsignedInteger('plan_id');
            $table->unsignedInteger('sets');
            $table->unsignedInteger('min_reps');
            $table->unsignedInteger('max_reps');
            $table->unsignedSmallInteger('order');
            $table->timestamps();
        });
    }
};
