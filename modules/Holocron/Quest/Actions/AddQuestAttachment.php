<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Illuminate\Http\UploadedFile;
use Modules\Holocron\Quest\Models\Quest;

final readonly class AddQuestAttachment
{
    public function handle(Quest $quest, UploadedFile $file): Quest
    {
        $storedPath = $file->store('quests', 'public');

        if ($storedPath) {
            $quest->update([
                'attachments' => $quest->attachments->push($storedPath),
            ]);
        }

        return $quest->refresh();
    }
}
