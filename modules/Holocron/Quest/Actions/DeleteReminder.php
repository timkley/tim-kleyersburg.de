<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Modules\Holocron\Quest\Models\Reminder;

final readonly class DeleteReminder
{
    public function handle(Reminder $reminder): void
    {
        $reminder->delete();
    }
}
