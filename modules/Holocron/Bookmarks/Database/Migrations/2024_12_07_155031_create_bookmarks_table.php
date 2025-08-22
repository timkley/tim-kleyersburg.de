<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookmarks', function (Blueprint $table): void {
            $table->id();
            $table->string('url');
            $table->binary('favicon')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();
        });
    }
};
