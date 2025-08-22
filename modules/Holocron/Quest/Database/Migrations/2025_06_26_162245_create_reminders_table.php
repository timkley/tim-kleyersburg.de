<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Holocron\Quest\Enums\ReminderType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quest_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('quest_id');
            $table->string('type')->default(ReminderType::Once);
            $table->dateTime('remind_at');
            $table->string('recurrence_pattern')->nullable();
            $table->dateTime('last_processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quest_reminders');
    }
};
