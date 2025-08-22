<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vocabulary_tests', function (Blueprint $table): void {
            $table->id();
            $table->json('word_ids');
            $table->json('correct_ids')->nullable();
            $table->json('wrong_ids')->nullable();
            $table->integer('error_count')->default(0);
            $table->boolean('finished')->default(false);
            $table->timestamps();
        });
    }
};
