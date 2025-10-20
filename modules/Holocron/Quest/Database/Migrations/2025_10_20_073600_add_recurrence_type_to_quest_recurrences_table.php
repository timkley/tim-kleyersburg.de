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
        Schema::table('quest_recurrences', function (Blueprint $table) {
            $table->string('recurrence_type')->default('recurrence_based')->after('every_x_days');
        });

        DB::table('quest_recurrences')
            ->whereNull('recurrence_type')
            ->update(['recurrence_type' => 'recurrence_based']);
    }
};
