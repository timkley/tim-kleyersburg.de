<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // get all intakes
        // group and sum by date
        // create daily goals
        // drop intakes

        DB::table('intakes')
            ->selectRaw('type, SUM(amount) as total, DATE(created_at) as date, unit, created_at, updated_at')
            ->groupBy('type', 'date')
            ->cursor()
            ->each(function ($intake) {
                $goal = $intake->type === 'water' ? 2350 : $intake->total;

                DB::table('daily_goals')->updateOrInsert(
                    [
                        'type' => $intake->type,
                        'unit' => $intake->unit,
                        'goal' => $goal,
                        'amount' => $intake->total,
                        'created_at' => $intake->created_at,
                        'updated_at' => $intake->updated_at,
                    ],
                    [
                        'date' => $intake->date,
                    ]
                );
            });

        Schema::dropIfExists('intakes');
    }
};
