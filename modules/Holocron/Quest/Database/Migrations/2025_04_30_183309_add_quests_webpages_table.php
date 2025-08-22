<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quest_webpage', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('quest_id');
            $table->unsignedInteger('webpage_id');
            $table->timestamps();
        });
    }
};
