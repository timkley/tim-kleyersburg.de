<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Holocron\Quest\Models\Quest;

beforeEach(function () {
    $this->headers = ['Authorization' => 'Bearer '.config('auth.bearer_token')];
});

it('uploads an attachment', function () {
    Storage::fake('public');

    $quest = Quest::factory()->create();
    $file = UploadedFile::fake()->image('photo.jpg');

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/attachments", [
            'file' => $file,
        ])
        ->assertSuccessful();

    $quest->refresh();
    expect($quest->attachments)->toHaveCount(1);
    Storage::disk('public')->assertExists($quest->attachments->first());
});

it('removes an attachment', function () {
    Storage::fake('public');

    $path = UploadedFile::fake()->image('photo.jpg')->store('quests', 'public');
    $quest = Quest::factory()->create(['attachments' => [$path]]);

    $this->withHeaders($this->headers)
        ->deleteJson("/api/holocron/quests/{$quest->id}/attachments", [
            'path' => $path,
        ])
        ->assertNoContent();

    $quest->refresh();
    expect($quest->attachments)->toHaveCount(0);
    Storage::disk('public')->assertMissing($path);
});
