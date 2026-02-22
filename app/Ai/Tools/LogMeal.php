<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Grind\Models\NutritionDay;
use Stringable;

class LogMeal implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Primary intent: log a newly consumed meal with macros for a day. Invoke when user says: "Logge Mahlzeit ...", "Trage Fruehstueck ein ...", or provides kcal/protein/fat/carbs to store. Do not invoke when: the user asks for reports, trends, or nutrition summaries without adding a meal.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $date = CarbonImmutable::parse($request['date']);

        $day = NutritionDay::query()->firstOrCreate(
            ['date' => $date],
            [
                'type' => $request['day_type'] ?? 'rest',
                'meals' => [],
                'total_kcal' => 0,
                'total_protein' => 0,
                'total_fat' => 0,
                'total_carbs' => 0,
            ],
        );

        $meal = array_filter([
            'name' => $request['name'],
            'time' => $request['time'] ?? null,
            'kcal' => $request['kcal'],
            'protein' => $request['protein'],
            'fat' => $request['fat'],
            'carbs' => $request['carbs'],
        ], fn ($value) => $value !== null);

        $meals = $day->meals;
        $meals[] = $meal;
        $day->meals = $meals;
        $day->save();
        $day->recalculateTotals();

        return sprintf(
            'Meal "%s" logged for %s. Daily totals: %d kcal, %dg protein, %dg fat, %dg carbs (%d meals total).',
            $request['name'],
            $request['date'],
            $day->total_kcal,
            $day->total_protein,
            $day->total_fat,
            $day->total_carbs,
            count($day->meals),
        );
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'date' => $schema->string()->required(),
            'name' => $schema->string()->required(),
            'kcal' => $schema->integer()->required(),
            'protein' => $schema->integer()->required(),
            'fat' => $schema->integer()->required(),
            'carbs' => $schema->integer()->required(),
            'time' => $schema->string(),
            'day_type' => $schema->string(),
        ];
    }
}
