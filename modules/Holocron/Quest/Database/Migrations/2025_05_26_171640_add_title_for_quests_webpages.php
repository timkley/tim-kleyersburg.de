<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quest_webpage', function (Blueprint $table) {
            $table->string('title')->after('webpage_id')->nullable();
        });
    }
};
