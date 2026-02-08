<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('print_queues', function (Blueprint $table) {
            $table->text('text')->nullable()->after('image');
            $table->string('image')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('print_queues', function (Blueprint $table) {
            $table->dropColumn('text');
            $table->string('image')->nullable(false)->change();
        });
    }
};
