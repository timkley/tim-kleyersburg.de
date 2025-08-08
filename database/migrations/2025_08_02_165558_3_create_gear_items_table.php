<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gear_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('gear_categories')->nullOnDelete();
            $table->string('name');
            $table->float('quantity_per_day')->default(0);
            $table->integer('quantity')->default(1);
            $table->json('properties')->nullable();
            $table->timestamps();
        });
    }
};
