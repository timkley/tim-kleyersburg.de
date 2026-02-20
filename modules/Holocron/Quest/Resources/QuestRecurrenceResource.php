<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Modules\Holocron\Quest\Models\QuestRecurrence */
final class QuestRecurrenceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (is_null($this->resource)) {
            return [];
        }

        return [
            'id' => $this->id,
            'quest_id' => $this->quest_id,
            'every_x_days' => $this->every_x_days,
            'recurrence_type' => $this->recurrence_type,
            'last_recurred_at' => $this->last_recurred_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
