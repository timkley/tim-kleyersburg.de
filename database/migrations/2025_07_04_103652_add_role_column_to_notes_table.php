<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quest_notes', function (Blueprint $table) {
            $table->text('content')->nullable()->change();
            $table->string('role')->after('status')->default('user');
        });
    }
};
