<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quests', function (Blueprint $table) {
            $table->date('date')->nullable()->unique()->after('id');
        });

        // update all quests that are accepted to have a date
        DB::table('quests')->where('accepted', true)->update(['date' => today()->format('Y-m-d')]);

        Schema::table('quests', function (Blueprint $table) {
            $table->dropColumn('accepted');
        });
    }
};
