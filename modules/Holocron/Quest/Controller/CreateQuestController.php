<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Holocron\Quest\Models\Quest;

class CreateQuestController
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required',
            'delete_after_print' => 'boolean',
        ]);

        Quest::query()->create($validated);

        return response()->json();
    }
}
