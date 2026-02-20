<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Controller\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Holocron\Quest\Actions\DeleteRecurrence;
use Modules\Holocron\Quest\Actions\SaveRecurrence;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Resources\QuestRecurrenceResource;

final class QuestRecurrenceController
{
    public function show(Quest $quest): JsonResponse
    {
        $recurrence = $quest->recurrence;

        if (is_null($recurrence)) {
            return response()->json(['data' => null]);
        }

        return response()->json(['data' => new QuestRecurrenceResource($recurrence)]);
    }

    public function store(Request $request, Quest $quest): JsonResponse
    {
        $recurrence = (new SaveRecurrence)->handle($quest, $request->all());

        return (new QuestRecurrenceResource($recurrence))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Quest $quest): JsonResponse
    {
        (new DeleteRecurrence)->handle($quest);

        return response()->json(null, 204);
    }
}
