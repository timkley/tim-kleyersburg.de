<?php

declare(strict_types=1);

namespace Modules\Holocron\Printer\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Holocron\Printer\Services\Printer;

class PrintSomethingController
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required',
        ]);

        Printer::print('holocron-printer::print', $validated);

        return response()->json([
            'message' => 'Print job created successfully.',
        ], 201);
    }
}
