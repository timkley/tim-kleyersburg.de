<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Controller;

use Illuminate\View\View;
use Modules\Holocron\Quest\Enums\QuestStatus;
use Modules\Holocron\Quest\Models\Quest;

class CompleteController
{
    public function __invoke(): View
    {
        $quest = Quest::query()->find(request()->get('id'));
        $quest->update(['status' => QuestStatus::Complete]);

        return view('holocron-quest::complete', [
            'quest' => $quest,
        ]);
    }
}
