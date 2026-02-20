<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Modules\Holocron\Quest\Models\Reminder */
final class ReminderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quest_id' => $this->quest_id,
            'type' => $this->type,
            'remind_at' => $this->remind_at->toIso8601String(),
            'recurrence_pattern' => $this->recurrence_pattern,
            'last_processed_at' => $this->last_processed_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
