<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Modules\Holocron\Quest\Models\Note;

final readonly class DeleteNote
{
    public function handle(Note $note): void
    {
        $note->delete();
    }
}
