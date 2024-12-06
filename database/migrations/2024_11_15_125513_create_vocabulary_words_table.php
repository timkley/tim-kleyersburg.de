<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vocabulary_words', function (Blueprint $table) {
            $table->id();
            $table->string('english');
            $table->string('german');
            $table->unsignedInteger('right')->default(0);
            $table->unsignedInteger('wrong')->default(0);
            $table->timestamps();
        });
    }
};
