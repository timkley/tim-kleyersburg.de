<?php

declare(strict_types=1);

namespace App\Enums\Holocron;

enum ReminderType: string
{
    case Once = 'once';
    case Cron = 'cron';
}
