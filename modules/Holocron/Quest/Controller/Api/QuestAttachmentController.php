<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Controller\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Holocron\Quest\Actions\AddQuestAttachment;
use Modules\Holocron\Quest\Actions\RemoveQuestAttachment;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Resources\QuestResource;

final class QuestAttachmentController
{
    public function store(Request $request, Quest $quest): QuestResource
    {
        $request->validate(['file' => ['required', 'file']]);

        $updated = (new AddQuestAttachment)->handle($quest, $request->file('file'));

        return new QuestResource($updated);
    }

    public function destroy(Request $request, Quest $quest): JsonResponse
    {
        $request->validate(['path' => ['required', 'string']]);

        (new RemoveQuestAttachment)->handle($quest, $request->input('path'));

        return response()->json(null, 204);
    }
}
