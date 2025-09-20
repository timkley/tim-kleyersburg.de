<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Holocron\Quest\Models\Quest;

class CreateQuestController
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required',
                'description' => 'nullable',
                'should_be_printed' => 'boolean',
                'delete_after_print' => 'boolean',
            ]);

            $quest = Quest::query()->create($validated);

            return response()->json([
                'message' => 'Quest created successfully',
                'quest' => $quest,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
