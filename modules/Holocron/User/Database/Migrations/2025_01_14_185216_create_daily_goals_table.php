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
        Schema::create('daily_goals', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->string('unit');
            $table->unsignedInteger('goal');
            $table->unsignedInteger('amount')->default(0);
            $table->date('date')->default(now());
            $table->timestamps();
        });
    }
};
