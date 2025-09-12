<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scrobbles', function (Blueprint $table) {
            $table->id();
            $table->string('artist');
            $table->string('track');
            $table->string('album')->nullable();
            $table->dateTime('played_at');
            $table->json('payload');

            $table->unique(['artist', 'track', 'played_at']);
        });
    }
};
