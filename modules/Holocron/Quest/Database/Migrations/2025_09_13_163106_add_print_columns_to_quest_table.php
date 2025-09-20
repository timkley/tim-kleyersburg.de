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
            $table->boolean('should_be_printed')->default(false)->after('status');
            $table->dateTime('printed_at')->nullable()->after('should_be_printed');
        });
    }
};
