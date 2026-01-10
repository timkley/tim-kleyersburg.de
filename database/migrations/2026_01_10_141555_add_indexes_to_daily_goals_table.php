<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_goals', function (Blueprint $table): void {
            $table->index(['type', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::table('daily_goals', function (Blueprint $table): void {
            $table->dropIndex(['type', 'date']);
            $table->dropIndex(['date']);
        });
    }
};
