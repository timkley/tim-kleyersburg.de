<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gear_journeys', function (Blueprint $table) {
            $table->dropColumn('participants');
            $table->json('properties')->nullable()->default('[]');
        });
    }

    public function down(): void
    {
        Schema::table('gear_journeys', function (Blueprint $table) {
            $table->dropColumn('properties');
            $table->json('participants')->nullable();
        });
    }
};
