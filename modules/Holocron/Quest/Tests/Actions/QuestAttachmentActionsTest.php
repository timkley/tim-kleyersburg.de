<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Holocron\Quest\Actions\AddQuestAttachment;
use Modules\Holocron\Quest\Actions\RemoveQuestAttachment;
use Modules\Holocron\Quest\Models\Quest;

it('adds an attachment to a quest', function () {
    Storage::fake('public');

    $quest = Quest::factory()->create(['attachments' => []]);
    $file = UploadedFile::fake()->image('photo.jpg');

    $result = (new AddQuestAttachment)->handle($quest, $file);

    expect($result->attachments)->toHaveCount(1);
    Storage::disk('public')->assertExists($result->attachments->first());
});

it('appends to existing attachments', function () {
    Storage::fake('public');

    $existingPath = 'quests/existing-file.jpg';
    $quest = Quest::factory()->create(['attachments' => [$existingPath]]);
    $file = UploadedFile::fake()->image('new-photo.jpg');

    $result = (new AddQuestAttachment)->handle($quest, $file);

    expect($result->attachments)->toHaveCount(2)
        ->and($result->attachments->first())->toBe($existingPath);
});

it('throws an exception when file storage fails', function () {
    Storage::fake('public');

    // Make the store method return false by using a read-only directory
    Storage::disk('public')->makeDirectory('quests');

    $quest = Quest::factory()->create(['attachments' => []]);

    // Create a file that will fail to store by mocking the disk
    $file = Mockery::mock(UploadedFile::class);
    $file->shouldReceive('store')
        ->with('quests', 'public')
        ->andReturn(false);

    expect(fn () => (new AddQuestAttachment)->handle($quest, $file))
        ->toThrow(RuntimeException::class, 'Failed to store attachment.');
});

it('removes an attachment and deletes the file', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('photo.jpg');
    $storedPath = $file->store('quests', 'public');

    $quest = Quest::factory()->create(['attachments' => [$storedPath]]);

    $result = (new RemoveQuestAttachment)->handle($quest, $storedPath);

    expect($result->attachments)->toHaveCount(0);
    Storage::disk('public')->assertMissing($storedPath);
});
