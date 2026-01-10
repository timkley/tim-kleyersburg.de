<?php

declare(strict_types=1);

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
        Schema::table('quests', function (Blueprint $table) {
            // For parent-child relationships (children lookup, noChildren scope)
            $table->index(['quest_id', 'completed_at'], 'quests_parent_completed_index');

            // For today's quests (date-based queries)
            $table->index(['date', 'daily', 'completed_at'], 'quests_date_daily_completed_index');

            // For notes queries
            $table->index(['is_note', 'quest_id'], 'quests_note_parent_index');

            // For general completed_at filtering
            $table->index('completed_at', 'quests_completed_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quests', function (Blueprint $table) {
            $table->dropIndex('quests_parent_completed_index');
            $table->dropIndex('quests_date_daily_completed_index');
            $table->dropIndex('quests_note_parent_index');
            $table->dropIndex('quests_completed_at_index');
        });
    }
};
