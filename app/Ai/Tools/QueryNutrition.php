<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Grind\Models\NutritionDay;
use Modules\Holocron\User\Models\UserSetting;
use Stringable;

class QueryNutrition implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Query nutrition data. Use query_type: "today" for today\'s meals and totals, "date" for a specific date, "week" for last 7 days overview, "average" for 7-day rolling averages vs targets.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return match ($request['query_type']) {
            'today' => $this->queryDate(today()->toDateString()),
            'date' => $this->queryDate($request['date'] ?? today()->toDateString()),
            'week' => $this->queryWeek(),
            'average' => $this->queryAverage(),
            default => 'Unknown query type. Use: today, date, week, or average.',
        };
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query_type' => $schema->string()->required(),
            'date' => $schema->string(),
        ];
    }

    private function queryDate(string $date): string
    {
        $day = NutritionDay::query()->whereDate('date', $date)->first();

        if (! $day) {
            return "No nutrition data for $date.";
        }

        $targets = UserSetting::first()?->nutrition_daily_targets[$day->type] ?? null;

        $meals = collect($day->meals)->map(fn (array $meal) => sprintf(
            '- %s%s: %d kcal, %dg P, %dg F, %dg C',
            isset($meal['time']) ? $meal['time'].' ' : '',
            $meal['name'],
            $meal['kcal'],
            $meal['protein'],
            $meal['fat'],
            $meal['carbs'],
        ))->implode("\n");

        $result = sprintf(
            "Date: %s | Type: %s%s\n\nMeals:\n%s\n\nTotals: %d kcal, %dg protein, %dg fat, %dg carbs",
            $date,
            $day->type,
            $day->training_label ? " ({$day->training_label})" : '',
            $meals,
            $day->total_kcal,
            $day->total_protein,
            $day->total_fat,
            $day->total_carbs,
        );

        if ($targets) {
            $result .= sprintf(
                "\nTargets: %d kcal, %dg protein, %dg fat, %dg carbs",
                $targets['kcal'],
                $targets['protein'],
                $targets['fat'],
                $targets['carbs'],
            );
        }

        return $result;
    }

    private function queryWeek(): string
    {
        $days = NutritionDay::query()
            ->whereDate('date', '>=', today()->subDays(6))
            ->whereDate('date', '<=', today())
            ->orderBy('date')
            ->get();

        if ($days->isEmpty()) {
            return 'No nutrition data for the last 7 days.';
        }

        return $days->map(fn (NutritionDay $day) => sprintf(
            '%s (%s): %d kcal, %dg P, %dg F, %dg C',
            $day->date->format('Y-m-d'),
            $day->type,
            $day->total_kcal,
            $day->total_protein,
            $day->total_fat,
            $day->total_carbs,
        ))->implode("\n");
    }

    private function queryAverage(): string
    {
        $days = NutritionDay::query()
            ->whereDate('date', '>=', today()->subDays(6))
            ->whereDate('date', '<=', today())
            ->get();

        if ($days->isEmpty()) {
            return 'No nutrition data for the last 7 days to average.';
        }

        $count = $days->count();
        $avgKcal = (int) round($days->sum('total_kcal') / $count);
        $avgProtein = (int) round($days->sum('total_protein') / $count);
        $avgFat = (int) round($days->sum('total_fat') / $count);
        $avgCarbs = (int) round($days->sum('total_carbs') / $count);

        $targets = UserSetting::first()?->nutrition_daily_targets;
        $result = sprintf(
            '7-day average (%d days): %d kcal, %dg protein, %dg fat, %dg carbs',
            $count,
            $avgKcal,
            $avgProtein,
            $avgFat,
            $avgCarbs,
        );

        if ($targets) {
            $result .= "\n\nTargets per day type:";
            foreach ($targets as $type => $t) {
                $result .= sprintf(
                    "\n  %s: %d kcal, %dg protein (target), %dg fat, %dg carbs",
                    $type,
                    $t['kcal'],
                    $t['protein'],
                    $t['fat'],
                    $t['carbs'],
                );
            }
        }

        return $result;
    }
}
