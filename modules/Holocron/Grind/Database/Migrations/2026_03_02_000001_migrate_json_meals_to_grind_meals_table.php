<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $days = DB::select('SELECT id, meals FROM grind_nutrition_days WHERE meals IS NOT NULL');

        foreach ($days as $day) {
            $meals = json_decode($day->meals, true);

            if (! is_array($meals)) {
                continue;
            }

            foreach ($meals as $meal) {
                DB::table('grind_meals')->insert([
                    'nutrition_day_id' => $day->id,
                    'name' => $meal['name'] ?? 'Unknown',
                    'time' => $meal['time'] ?? null,
                    'kcal' => $meal['kcal'] ?? 0,
                    'protein' => $meal['protein'] ?? 0,
                    'fat' => $meal['fat'] ?? 0,
                    'carbs' => $meal['carbs'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
