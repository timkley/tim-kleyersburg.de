<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Controller;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Holocron\Grind\Models\HealthData;

class SyncHealthDataController
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'data' => 'required|array',
                'data.metrics' => 'required|array',
                'data.metrics.*.name' => 'required|string',
                'data.metrics.*.units' => 'required|string',
                'data.metrics.*.data' => 'required|array',
                'data.metrics.*.data.*.date' => 'required|string',
                'data.metrics.*.data.*.qty' => 'required|numeric',
                'data.metrics.*.data.*.source' => 'nullable|string',
            ]);

            $records = [];
            $processedCount = 0;

            foreach ($validated['data']['metrics'] as $metric) {
                $metricName = $metric['name'];
                $units = $metric['units'];

                foreach ($metric['data'] as $dataPoint) {
                    $date = Carbon::parse($dataPoint['date'])->toDateString();

                    $records[] = [
                        'name' => $metricName,
                        'units' => $units,
                        'qty' => (float) $dataPoint['qty'],
                        'date' => $date,
                        'source' => $dataPoint['source'] ?? null,
                        'original_payload' => $dataPoint,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $processedCount++;
                }
            }

            $affectedRows = 0;
            if (! empty($records)) {
                DB::transaction(function () use ($records, &$affectedRows) {
                    $affectedRows = HealthData::query()->upsert(
                        $records,
                        ['date', 'name'],
                        ['qty', 'units', 'source', 'original_payload', 'updated_at']
                    );
                });
            }

            return response()->json([
                'message' => 'Health data synced successfully',
                'processed' => $processedCount,
                'affected_rows' => $affectedRows,
                'metrics_synced' => count($validated['data']['metrics']),
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while processing health data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
