<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Modules\Holocron\Quest\Models\Quest */
final class QuestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quest_id' => $this->quest_id,
            'name' => $this->name,
            'description' => $this->description,
            'date' => $this->date?->format('Y-m-d'),
            'daily' => $this->daily,
            'is_note' => $this->is_note,
            'accepted' => $this->accepted,
            'completed_at' => $this->completed_at?->toIso8601String(),
            'should_be_printed' => $this->should_be_printed,
            'attachments' => $this->attachments,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'children' => self::collection($this->whenLoaded('children')),
            'notes' => NoteResource::collection($this->whenLoaded('notes')),
            'webpages' => WebpageResource::collection($this->whenLoaded('webpages')),
            'reminders' => ReminderResource::collection($this->whenLoaded('reminders')),
            'recurrence' => $this->whenLoaded('recurrence', fn () => new QuestRecurrenceResource($this->recurrence)),
        ];
    }
}
