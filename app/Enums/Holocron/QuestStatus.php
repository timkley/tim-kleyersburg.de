<?php

declare(strict_types=1);

namespace App\Enums\Holocron;

enum QuestStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Complete = 'complete';
    case Note = 'note';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Offen',
            self::InProgress => 'In Bearbeitung',
            self::Complete => 'Fertig',
            self::Note => 'Notiz',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Open => 'circle-dashed',
            self::InProgress => 'circle-dot-dashed',
            self::Complete => 'circle-dot',
            self::Note => 'document-text',
        };
    }
}
