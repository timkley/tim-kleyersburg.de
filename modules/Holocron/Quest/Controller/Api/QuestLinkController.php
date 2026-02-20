<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Controller\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Holocron\Quest\Actions\AddQuestLink;
use Modules\Holocron\Quest\Actions\DeleteQuestLink;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Resources\QuestResource;
use Modules\Holocron\Quest\Resources\WebpageResource;

final class QuestLinkController
{
    public function index(Quest $quest): AnonymousResourceCollection
    {
        return WebpageResource::collection($quest->webpages);
    }

    public function store(Request $request, Quest $quest): JsonResponse
    {
        $updated = (new AddQuestLink)->handle($quest, $request->all());

        return (new QuestResource($updated))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Quest $quest, int $pivotId): JsonResponse
    {
        (new DeleteQuestLink)->handle($quest, $pivotId);

        return response()->json(null, 204);
    }
}
