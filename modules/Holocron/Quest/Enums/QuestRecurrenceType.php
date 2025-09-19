<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Enums;

enum QuestRecurrenceType: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
}
