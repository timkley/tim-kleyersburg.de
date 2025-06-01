<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experience_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->integer('amount');
            $table->string('type');
            $table->string('identifier');
            $table->text('description');
            $table->timestamps();
        });
    }
};
