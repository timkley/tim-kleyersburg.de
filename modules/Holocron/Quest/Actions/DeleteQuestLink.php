<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Modules\Holocron\Quest\Models\Quest;

final readonly class DeleteQuestLink
{
    public function handle(Quest $quest, int $pivotId): void
    {
        $quest->webpages()->wherePivot('id', $pivotId)->detach();
    }
}
