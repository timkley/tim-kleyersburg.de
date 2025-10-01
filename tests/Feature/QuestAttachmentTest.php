<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Holocron\Quest\Livewire\Show;
use Modules\Holocron\Quest\Models\Quest;

it('can upload attachments', function () {
    Storage::fake('public');

    $quest = Quest::factory()->create();
    $file = UploadedFile::fake()->image('photo.jpg');

    Livewire::test(Show::class, ['quest' => $quest])
        ->set('newAttachments', [$file]);

    $quest->refresh();

    expect($quest->attachments)->toHaveCount(1);
    Storage::disk('public')->assertExists($quest->attachments->first());
});

it('can delete an attachment', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('photo.jpg')->store('quests', 'public');
    $quest = Quest::factory()->create(['attachments' => [$file]]);

    expect($quest->attachments)->toHaveCount(1);
    Storage::disk('public')->assertExists($file);

    Livewire::test(Show::class, ['quest' => $quest])
        ->call('removeAttachment', $file);

    $quest->refresh();

    expect($quest->attachments)->toHaveCount(0);
    Storage::disk('public')->assertMissing($file);
});
