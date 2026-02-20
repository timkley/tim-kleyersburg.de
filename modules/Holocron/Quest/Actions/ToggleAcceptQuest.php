<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Modules\Holocron\Quest\Models\Quest;

final readonly class ToggleAcceptQuest
{
    public function handle(Quest $quest): Quest
    {
        $quest->update(['date' => $quest->date ? null : now()]);

        return $quest->refresh();
    }
}
