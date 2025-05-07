<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quest_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(App\Models\Holocron\Quest::class);
            $table->text('content');
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }
};
