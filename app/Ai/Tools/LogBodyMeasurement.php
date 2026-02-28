<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Grind\Models\BodyMeasurement;
use Stringable;

class LogBodyMeasurement implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Primary intent: log a body measurement (weight, body fat, etc.) for a date. Invoke when user says: "Ich wiege ...", "Einwaage ...", "Mein Gewicht heute ...", or provides weight/body composition data to store. Do not invoke when: the user asks for trends, history, or comparisons without providing new data.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $date = CarbonImmutable::parse($request['date']);

        $attributes = array_filter([
            'weight' => $request['weight'],
            'body_fat' => $request['body_fat'] ?? null,
            'muscle_mass' => $request['muscle_mass'] ?? null,
            'visceral_fat' => $request['visceral_fat'] ?? null,
            'bmi' => $request['bmi'] ?? null,
            'body_water' => $request['body_water'] ?? null,
        ], fn (mixed $value) => $value !== null);

        $measurement = BodyMeasurement::query()->updateOrCreate(
            ['date' => $date],
            $attributes,
        );

        $parts = [sprintf('%.1f kg', $measurement->weight)];

        if ($measurement->body_fat !== null) {
            $parts[] = sprintf('%.1f%% body fat', $measurement->body_fat);
        }
        if ($measurement->muscle_mass !== null) {
            $parts[] = sprintf('%.1f kg muscle', $measurement->muscle_mass);
        }

        $result = sprintf(
            'Body measurement logged for %s: %s.',
            $request['date'],
            implode(', ', $parts),
        );

        $previous = BodyMeasurement::query()
            ->whereDate('date', '<', $date)
            ->orderByDesc('date')
            ->first();

        if ($previous) {
            $delta = round((float) $measurement->weight - (float) $previous->weight, 1);
            $sign = $delta >= 0 ? '+' : '';
            $result .= sprintf(
                ' Delta since %s: %s%s kg.',
                $previous->date->format('Y-m-d'),
                $sign,
                $delta,
            );
        }

        return $result;
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'date' => $schema->string()->required(),
            'weight' => $schema->number()->required(),
            'body_fat' => $schema->number(),
            'muscle_mass' => $schema->number(),
            'visceral_fat' => $schema->integer(),
            'bmi' => $schema->number(),
            'body_water' => $schema->number(),
        ];
    }
}
