<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grind_sets', function (Blueprint $table) {
            $table->dropColumn(['workout_id', 'exercise_id']);
        });
    }
};
