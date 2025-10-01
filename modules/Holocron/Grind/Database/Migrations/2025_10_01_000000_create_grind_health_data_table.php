<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grind_health_data', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('units');
            $table->decimal('qty', 10, 2);
            $table->date('date');
            $table->string('source')->nullable();
            $table->json('original_payload');
            $table->timestamps();

            $table->unique(['date', 'name']);
        });
    }
};
