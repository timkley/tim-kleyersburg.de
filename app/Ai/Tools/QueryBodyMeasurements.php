<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Grind\Models\BodyMeasurement;
use Stringable;

class QueryBodyMeasurements implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Primary intent: report body measurement history, trends, and progress. Invoke when user says: "Mein Gewicht?", "Gewichtsverlauf", "Wie schwer bin ich?", "Koerper-Trend", or asks about weight/body composition data. Do not invoke when: the user is providing new measurement data to store.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return match ($request['query_type']) {
            'latest' => $this->queryLatest(),
            'date' => $this->queryDate($request['date'] ?? today()->toDateString()),
            'trend' => $this->queryTrend(),
            default => 'Unknown query type. Use: latest, date, or trend.',
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

    private function formatMeasurement(BodyMeasurement $m): string
    {
        $parts = [sprintf('%.1f kg', $m->weight)];

        if ($m->body_fat !== null) {
            $parts[] = sprintf('%.1f%% body fat', $m->body_fat);
        }
        if ($m->muscle_mass !== null) {
            $parts[] = sprintf('%.1f kg muscle', $m->muscle_mass);
        }
        if ($m->visceral_fat !== null) {
            $parts[] = sprintf('visceral fat %d', $m->visceral_fat);
        }
        if ($m->bmi !== null) {
            $parts[] = sprintf('BMI %.1f', $m->bmi);
        }
        if ($m->body_water !== null) {
            $parts[] = sprintf('%.1f%% water', $m->body_water);
        }

        return implode(', ', $parts);
    }

    private function queryLatest(): string
    {
        $measurement = BodyMeasurement::query()->orderByDesc('date')->first();

        if (! $measurement) {
            return 'No body measurements recorded yet.';
        }

        return sprintf(
            'Latest measurement (%s): %s',
            $measurement->date->format('Y-m-d'),
            $this->formatMeasurement($measurement),
        );
    }

    private function queryDate(string $date): string
    {
        $measurement = BodyMeasurement::query()->whereDate('date', $date)->first();

        if (! $measurement) {
            return "No body measurement for $date.";
        }

        return sprintf(
            'Measurement for %s: %s',
            $date,
            $this->formatMeasurement($measurement),
        );
    }

    private function queryTrend(): string
    {
        $measurements = BodyMeasurement::query()
            ->orderByDesc('date')
            ->limit(7)
            ->get()
            ->sortBy('date')
            ->values();

        if ($measurements->isEmpty()) {
            return 'No body measurements recorded yet.';
        }

        $lines = [];
        $previous = null;

        foreach ($measurements as $m) {
            $line = sprintf('%s: %.1f kg', $m->date->format('Y-m-d'), $m->weight);

            if ($previous) {
                $delta = round((float) $m->weight - (float) $previous->weight, 1);
                $sign = $delta >= 0 ? '+' : '';
                $line .= sprintf(' (%s%s)', $sign, $delta);
            }

            if ($m->body_fat !== null) {
                $line .= sprintf(' | %.1f%% BF', $m->body_fat);
            }

            $lines[] = $line;
            $previous = $m;
        }

        $first = $measurements->first();
        $last = $measurements->last();
        $totalDelta = round((float) $last->weight - (float) $first->weight, 1);
        $sign = $totalDelta >= 0 ? '+' : '';

        $lines[] = sprintf(
            "\nTotal change: %s%s kg over %d measurements.",
            $sign,
            $totalDelta,
            $measurements->count(),
        );

        return implode("\n", $lines);
    }
}
