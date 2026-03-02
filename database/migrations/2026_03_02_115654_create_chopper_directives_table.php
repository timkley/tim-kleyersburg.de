<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chopper_directives', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->datetime('deactivated_at')->nullable();
            $table->timestamps();
        });
    }
};
