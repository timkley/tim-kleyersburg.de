<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quests', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Modules\Holocron\Quest\Models\Quest::class)->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });
    }
};
