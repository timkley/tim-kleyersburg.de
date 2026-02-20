<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Modules\Holocron\Bookmarks\Models\Webpage */
final class WebpageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'title' => $this->whenPivotLoaded('quest_webpage', fn () => $this->pivot->title ?? $this->title),
            'pivot_id' => $this->whenPivotLoaded('quest_webpage', fn () => $this->pivot->id),
        ];
    }
}
