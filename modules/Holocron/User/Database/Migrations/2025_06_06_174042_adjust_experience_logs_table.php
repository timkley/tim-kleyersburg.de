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
        Schema::table('experience_logs', function (Blueprint $table) {
            $table->unsignedInteger('identifier')->change();
            $table->dropColumn('description');
        });

        foreach (DB::table('experience_logs')->select(['id', 'type'])->get() as $log) {
            DB::table('experience_logs')
                ->where('id', $log->id)
                ->update([
                    'type' => str_replace('-', '_', $log->type),
                ]);
        }
    }
};
