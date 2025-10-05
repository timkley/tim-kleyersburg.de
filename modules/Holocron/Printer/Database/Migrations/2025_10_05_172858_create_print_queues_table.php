<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_queues', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->json('actions')->default('[]');
            $table->dateTime('printed_at')->nullable()->default(null);
            $table->timestamps();
        });
    }
};
