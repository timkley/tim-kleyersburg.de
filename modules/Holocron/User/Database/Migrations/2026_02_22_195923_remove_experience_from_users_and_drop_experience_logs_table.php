<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'experience')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('experience');
            });
        }

        Schema::dropIfExists('experience_logs');
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'experience')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->integer('experience')->default(0);
            });
        }

        if (! Schema::hasTable('experience_logs')) {
            Schema::create('experience_logs', function (Blueprint $table): void {
                $table->id();
                $table->unsignedInteger('user_id');
                $table->integer('amount');
                $table->string('type');
                $table->unsignedInteger('identifier');
                $table->timestamps();
            });
        }
    }
};
