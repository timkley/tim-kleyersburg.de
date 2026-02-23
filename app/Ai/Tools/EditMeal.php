<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Grind\Models\NutritionDay;
use Stringable;

class EditMeal implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Primary intent: update an existing logged meal by date and meal index. Invoke when user says: "Bearbeite Mahlzeit ...", "Aendere Eintrag ...", or asks to correct macros/time of an existing meal. Do not invoke when: the user logs a brand-new meal or asks for nutrition summaries.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $date = CarbonImmutable::parse($request['date']);

        $day = NutritionDay::query()->whereDate('date', $date)->first();

        if (! $day) {
            return "No nutrition data for {$request['date']}.";
        }

        $mealIndex = (int) $request['meal_index'];
        $meals = $day->meals ?? [];

        if (! array_key_exists($mealIndex, $meals)) {
            return "Meal index {$mealIndex} not found for {$request['date']}.";
        }

        $updatedMeal = $meals[$mealIndex];

        if ($request->offsetExists('name')) {
            $updatedMeal['name'] = $request['name'];
        }

        if ($request->offsetExists('time')) {
            $time = $request['time'];

            if ($time === '') {
                unset($updatedMeal['time']);
            } else {
                $updatedMeal['time'] = $time;
            }
        }

        if ($request->offsetExists('kcal')) {
            $updatedMeal['kcal'] = (int) $request['kcal'];
        }

        if ($request->offsetExists('protein')) {
            $updatedMeal['protein'] = (int) $request['protein'];
        }

        if ($request->offsetExists('fat')) {
            $updatedMeal['fat'] = (int) $request['fat'];
        }

        if ($request->offsetExists('carbs')) {
            $updatedMeal['carbs'] = (int) $request['carbs'];
        }

        $meals[$mealIndex] = $updatedMeal;

        $day->meals = array_values($meals);
        $day->save();
        $day->recalculateTotals();

        return sprintf(
            'Meal at index %d updated for %s. Daily totals: %d kcal, %dg protein, %dg fat, %dg carbs (%d meals total).',
            $mealIndex,
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
            'meal_index' => $schema->integer()->required(),
            'name' => $schema->string(),
            'time' => $schema->string(),
            'kcal' => $schema->integer(),
            'protein' => $schema->integer(),
            'fat' => $schema->integer(),
            'carbs' => $schema->integer(),
        ];
    }
}
