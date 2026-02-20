<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Controller\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Holocron\Quest\Actions\DeleteReminder;
use Modules\Holocron\Quest\Actions\SaveReminder;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\Reminder;
use Modules\Holocron\Quest\Resources\ReminderResource;

final class QuestReminderController
{
    public function index(Quest $quest): AnonymousResourceCollection
    {
        return ReminderResource::collection($quest->reminders);
    }

    public function store(Request $request, Quest $quest): JsonResponse
    {
        $reminder = (new SaveReminder)->handle($quest, $request->all());

        return (new ReminderResource($reminder))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Quest $quest, Reminder $reminder): JsonResponse
    {
        (new DeleteReminder)->handle($reminder);

        return response()->json(null, 204);
    }
}
