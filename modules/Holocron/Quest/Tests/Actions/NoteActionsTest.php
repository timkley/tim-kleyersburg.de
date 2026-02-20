<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Actions\CreateNote;
use Modules\Holocron\Quest\Actions\DeleteNote;
use Modules\Holocron\Quest\Models\Note;
use Modules\Holocron\Quest\Models\Quest;

it('creates a note with default user role', function () {
    $quest = Quest::factory()->create();

    $note = (new CreateNote)->handle($quest, ['content' => 'My note content']);

    expect($note)->toBeInstanceOf(Note::class)
        ->and($note->content)->toBe('My note content')
        ->and($note->role)->toBe('user')
        ->and($note->quest_id)->toBe($quest->id);
});

it('creates a note with a custom role', function () {
    $quest = Quest::factory()->create();

    $note = (new CreateNote)->handle($quest, [
        'content' => 'AI response',
        'role' => 'assistant',
    ]);

    expect($note->role)->toBe('assistant');
});

it('validates that content is required for a note', function () {
    $quest = Quest::factory()->create();

    (new CreateNote)->handle($quest, []);
})->throws(Illuminate\Validation\ValidationException::class);

it('deletes a note', function () {
    $note = Note::factory()->create();

    (new DeleteNote)->handle($note);

    expect(Note::find($note->id))->toBeNull();
});
