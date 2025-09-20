<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quests', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_note')->default(false);
        });

        DB::table('quests')->where('status', 'complete')->update([
            'completed_at' => DB::raw('updated_at'),
        ]);

        DB::table('quests')->where('status', 'note')->update([
            'is_note' => true,
        ]);

        Schema::table('quests', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
