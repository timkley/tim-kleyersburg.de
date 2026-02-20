<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Illuminate\Support\Facades\Validator;
use Modules\Holocron\Quest\Models\Note;
use Modules\Holocron\Quest\Models\Quest;

final readonly class CreateNote
{
    public function handle(Quest $quest, array $data): Note
    {
        $validated = Validator::make($data, [
            'content' => ['required', 'string'],
            'role' => ['sometimes', 'string', 'in:user,assistant'],
        ])->validate();

        return $quest->notes()->create([
            'content' => $validated['content'],
            'role' => $validated['role'] ?? 'user',
        ]);
    }
}
