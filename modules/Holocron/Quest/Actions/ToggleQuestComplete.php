<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Modules\Holocron\Quest\Models\Quest;

final readonly class ToggleQuestComplete
{
    public function handle(Quest $quest): Quest
    {
        if ($quest->isCompleted()) {
            $quest->update(['completed_at' => null]);
        } else {
            $quest->complete();
        }

        return $quest->refresh();
    }
}
