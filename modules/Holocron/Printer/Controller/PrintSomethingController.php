<?php

declare(strict_types=1);

namespace Modules\Holocron\Printer\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Holocron\Printer\Model\PrintQueue;
use Modules\Holocron\Printer\Services\Printer;

class PrintSomethingController
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required_without:text|string',
            'text' => 'required_without:content|string',
        ]);

        // Text-only print job: skip image generation, store text directly
        if (isset($validated['text'])) {
            PrintQueue::create([
                'text' => $validated['text'],
                'actions' => [],
            ]);

            return response()->json([
                'message' => 'Text print job created successfully.',
            ], 201);
        }

        // Image-based print job (existing behavior)
        Printer::print('holocron-printer::print', $validated);

        return response()->json([
            'message' => 'Print job created successfully.',
        ], 201);
    }
}
