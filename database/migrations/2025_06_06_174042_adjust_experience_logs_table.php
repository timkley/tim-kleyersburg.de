<?php

declare(strict_types=1);

use App\Models\ExperienceLog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experience_logs', function (Blueprint $table) {
            $table->unsignedInteger('identifier')->change();
            $table->dropColumn('description');
        });

        foreach (ExperienceLog::all() as $log) {
            $log->type = str_replace('-', '_', $log->getRawOriginal('type'));
            $log->save();
        }
    }
};
