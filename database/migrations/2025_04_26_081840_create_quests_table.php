<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quests', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(App\Models\Holocron\Quest::class)->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default(App\Enums\Holocron\QuestStatus::Open);
            $table->timestamps();
        });
    }
};
