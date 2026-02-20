<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Controller\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Holocron\Quest\Actions\CreateQuest;
use Modules\Holocron\Quest\Actions\DeleteQuest;
use Modules\Holocron\Quest\Actions\MoveQuest;
use Modules\Holocron\Quest\Actions\PrintQuest;
use Modules\Holocron\Quest\Actions\ToggleAcceptQuest;
use Modules\Holocron\Quest\Actions\ToggleQuestComplete;
use Modules\Holocron\Quest\Actions\UpdateQuest;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Resources\QuestResource;

final class QuestController
{
    public function index(): AnonymousResourceCollection
    {
        return QuestResource::collection(Quest::all());
    }

    public function store(Request $request): JsonResponse
    {
        $quest = (new CreateQuest)->handle($request->all());

        return (new QuestResource($quest))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Quest $quest): QuestResource
    {
        $includes = array_filter(explode(',', $request->query('include', '')));
        $allowed = ['children', 'notes', 'webpages', 'reminders', 'recurrence'];
        $validIncludes = array_intersect($includes, $allowed);

        if (! empty($validIncludes)) {
            $quest->load($validIncludes);
        }

        return new QuestResource($quest);
    }

    public function update(Request $request, Quest $quest): QuestResource
    {
        $updated = (new UpdateQuest)->handle($quest, $request->all());

        return new QuestResource($updated);
    }

    public function destroy(Quest $quest): JsonResponse
    {
        (new DeleteQuest)->handle($quest);

        return response()->json(null, 204);
    }

    public function complete(Quest $quest): QuestResource
    {
        $quest = (new ToggleQuestComplete)->handle($quest);

        return new QuestResource($quest);
    }

    public function move(Request $request, Quest $quest): QuestResource
    {
        $quest = (new MoveQuest)->handle($quest, $request->all());

        return new QuestResource($quest);
    }

    public function print(Quest $quest): QuestResource
    {
        $quest = (new PrintQuest)->handle($quest);

        return new QuestResource($quest);
    }

    public function accept(Quest $quest): QuestResource
    {
        $quest = (new ToggleAcceptQuest)->handle($quest);

        return new QuestResource($quest);
    }
}
