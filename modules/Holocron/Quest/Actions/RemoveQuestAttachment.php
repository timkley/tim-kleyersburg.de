<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Illuminate\Support\Facades\Storage;
use Modules\Holocron\Quest\Models\Quest;

final readonly class RemoveQuestAttachment
{
    public function handle(Quest $quest, string $path): Quest
    {
        Storage::disk('public')->delete($path);

        $quest->update([
            'attachments' => $quest->attachments->filter(fn (string $a) => $a !== $path)->values(),
        ]);

        return $quest->refresh();
    }
}
