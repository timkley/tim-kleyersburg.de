<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grind_nutrition_days', function (Blueprint $table) {
            $table->dropColumn(['meals', 'total_kcal', 'total_protein', 'total_fat', 'total_carbs']);
        });
    }
};
