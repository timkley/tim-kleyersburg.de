<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Modules\Holocron\Quest\Models\Quest;

final readonly class DeleteRecurrence
{
    public function handle(Quest $quest): void
    {
        $quest->recurrence()->delete();
    }
}
