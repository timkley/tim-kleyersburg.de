<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Controller\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Holocron\Quest\Actions\CreateNote;
use Modules\Holocron\Quest\Actions\DeleteNote;
use Modules\Holocron\Quest\Models\Note;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Resources\NoteResource;

final class QuestNoteController
{
    public function index(Quest $quest): AnonymousResourceCollection
    {
        return NoteResource::collection($quest->notes);
    }

    public function store(Request $request, Quest $quest): JsonResponse
    {
        $note = (new CreateNote)->handle($quest, $request->all());

        return (new NoteResource($note))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Quest $quest, Note $note): JsonResponse
    {
        (new DeleteNote)->handle($note);

        return response()->json(null, 204);
    }
}
