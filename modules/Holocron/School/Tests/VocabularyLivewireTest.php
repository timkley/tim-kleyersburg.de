<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Modules\Holocron\School\Models\VocabularyTest;
use Modules\Holocron\School\Models\VocabularyWord;
use Modules\Holocron\User\Models\User;

it('renders the vocabulary page with words and tests', function () {
    VocabularyWord::factory()->count(3)->create();

    Livewire::actingAs(User::factory()->create())
        ->test('holocron.school.vocabulary')
        ->assertOk();
});

it('validates that german and english are required when adding a word', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('holocron.school.vocabulary')
        ->set('german', '')
        ->set('english', '')
        ->call('addWord')
        ->assertHasErrors(['german', 'english']);
});

it('deletes a vocabulary word', function () {
    $word = VocabularyWord::factory()->create();

    Livewire::actingAs(User::factory()->create())
        ->test('holocron.school.vocabulary')
        ->call('deleteWord', $word->id);

    expect(VocabularyWord::find($word->id))->toBeNull();
});

it('starts a vocabulary test with selected words', function () {
    VocabularyWord::factory()->count(10)->create();

    $response = Livewire::actingAs(User::factory()->create())
        ->test('holocron.school.vocabulary')
        ->call('startTest', 10);

    $test = VocabularyTest::latest()->first();
    expect($test)->not->toBeNull()
        ->and($test->word_ids)->toHaveCount(10);
});

it('allows Tim to delete a test', function () {
    $tim = User::factory()->create(['email' => 'timkley@gmail.com']);
    $words = VocabularyWord::factory()->count(5)->create();
    $test = VocabularyTest::factory()->create([
        'word_ids' => $words->pluck('id')->toArray(),
    ]);

    Livewire::actingAs($tim)
        ->test('holocron.school.vocabulary')
        ->call('deleteTest', $test->id);

    expect(VocabularyTest::find($test->id))->toBeNull();
});

it('checks the isTim gate when deleting a test', function () {
    $user = User::factory()->create(['email' => 'someone@example.com']);
    $words = VocabularyWord::factory()->count(5)->create();
    $test = VocabularyTest::factory()->create([
        'word_ids' => $words->pluck('id')->toArray(),
    ]);

    Gate::shouldReceive('allows')->with('isTim')->once()->andReturn(false);

    Livewire::actingAs($user)
        ->test('holocron.school.vocabulary')
        ->call('deleteTest', $test->id)
        ->assertForbidden();

    expect(VocabularyTest::find($test->id))->not->toBeNull();
});

it('resets form fields after adding a word', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('holocron.school.vocabulary')
        ->set('german', 'Schule')
        ->set('english', 'School')
        ->call('addWord')
        ->assertSet('german', '')
        ->assertSet('english', '');
});
