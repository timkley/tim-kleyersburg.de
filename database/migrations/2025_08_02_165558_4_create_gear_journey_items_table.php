<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gear_journey_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journey_id');
            $table->unsignedBigInteger('item_id');
            $table->integer('quantity');
            $table->boolean('packed_for_departure')->default(false);
            $table->boolean('packed_for_return')->default(false);
            $table->timestamps();
        });
    }
};