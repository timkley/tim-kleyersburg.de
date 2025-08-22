<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Enums;

enum ReminderType: string
{
    case Once = 'once';
    case Cron = 'cron';
}
