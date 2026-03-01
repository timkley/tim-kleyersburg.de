<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Quest\Livewire\Components\Note;
use Modules\Holocron\Quest\Models\Note as NoteModel;
use Modules\Holocron\Quest\Models\Quest;

it('renders a note component', function () {
    $quest = Quest::factory()->create();
    $note = NoteModel::factory()->for($quest)->create([
        'content' => '<p>Hello World</p>',
        'role' => 'user',
    ]);

    Livewire::test(Note::class, ['note' => $note])
        ->assertStatus(200)
        ->assertSee('Hello World');
});

it('renders an assistant note with offset styling', function () {
    $quest = Quest::factory()->create();
    $note = NoteModel::factory()->for($quest)->create([
        'content' => 'AI response',
        'role' => 'assistant',
    ]);

    Livewire::test(Note::class, ['note' => $note])
        ->assertStatus(200)
        ->assertSee('AI response');
});

it('displays the note creation date', function () {
    $quest = Quest::factory()->create();
    $note = NoteModel::factory()->for($quest)->create([
        'created_at' => '2026-02-15 14:30:00',
    ]);

    Livewire::test(Note::class, ['note' => $note])
        ->assertSee('15.02.2026 14:30');
});
