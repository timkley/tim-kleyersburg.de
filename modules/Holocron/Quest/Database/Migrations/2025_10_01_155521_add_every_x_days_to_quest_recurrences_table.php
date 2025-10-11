<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quest_recurrences', function (Blueprint $table) {
            $table->unsignedInteger('every_x_days')->default(1)->after('quest_id');
        });

        DB::table('quest_recurrences')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $days = match ($row->type) {
                    'daily' => $row->value,
                    'weekly' => $row->value * 7,
                    'monthly' => $row->value * 30, // simple approximation
                    default => 1,
                };
                DB::table('quest_recurrences')
                    ->where('id', $row->id)
                    ->update(['every_x_days' => $days]);
            }
        });

        Schema::table('quest_recurrences', function (Blueprint $table) {
            $table->dropColumn(['type', 'value']);
        });
    }
};
