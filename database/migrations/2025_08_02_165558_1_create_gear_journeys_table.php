<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gear_journeys', function (Blueprint $table) {
            $table->id();
            $table->string('destination');
            $table->date('starts_at');
            $table->date('ends_at');
            $table->json('participants');
            $table->timestamps();
        });
    }
};
