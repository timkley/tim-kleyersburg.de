<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Modules\Holocron\Quest\Models\Quest;

final readonly class PrintQuest
{
    public function handle(Quest $quest): Quest
    {
        $quest->update(['should_be_printed' => true]);

        return $quest->refresh();
    }
}
