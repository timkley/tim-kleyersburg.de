<?php

declare(strict_types=1);

use Modules\Holocron\School\Models\VocabularyTest;
use Modules\Holocron\School\Models\VocabularyWord;
use Modules\Holocron\User\Models\User;

it('knows the score of a vocabulary', function (int $right, int $wrong, int $result) {
    $word = VocabularyWord::factory()->make([
        'right' => $right,
        'wrong' => $wrong,
    ]);

    expect($word->score())->toBe($result);
})->with([
    [5, 3, 2],
    [5, 7, -2],
]);

it('can create a test from a list of words', function () {
    $words = VocabularyWord::factory()->count(10)->create();

    $test = VocabularyTest::factory()->create([
        'word_ids' => $words->pluck('id')->toArray(),
    ]);

    expect($test->word_ids->count())->toBe(10);
});

it('can move a word from the words list to the correct list', function () {
    $words = VocabularyWord::factory()->count(10)->create();
    $test = VocabularyTest::factory()->create([
        'word_ids' => $words->pluck('id')->toArray(),
    ]);

    $test->markAsCorrect($words[0]->id);
    $test->markAsWrong($words[1]->id);

    expect($test->word_ids->count())->toBe(10);
    expect($test->correct_ids->count())->toBe(1);
    expect($test->wrong_ids->count())->toBe(1);
});

it('knows the status of a test', function () {
    $words = VocabularyWord::factory()->count(10)->create();

    $test = VocabularyTest::factory()->create([
        'word_ids' => $words->pluck('id')->toArray(),
    ]);

    expect($test->finished)->toBeFalse();

    foreach ($words as $word) {
        $test->markAsCorrect($word->id);
    }

    expect($test->fresh()->finished)->toBeTrue();
});

it('can add vocabulary words', function () {
    Livewire\Livewire::test('holocron.school.vocabulary')
        ->set('german', 'Haus')
        ->set('english', 'House')
        ->call('addWord');

    expect(VocabularyWord::whereGerman('Haus')->whereEnglish('House')->exists())->toBeTrue();
});

it('has a correct words relationship', function () {
    $words = VocabularyWord::factory()->count(10)->create();
    $test = VocabularyTest::factory()->create([
        'word_ids' => $words->pluck('id')->toArray(),
    ]);

    expect($test->words())->toBeInstanceOf(Illuminate\Support\Collection::class)->toHaveCount(10);
});

it('can mark words as right or wrong', function () {
    $words = VocabularyWord::factory()->count(10)->create();
    $test = VocabularyTest::factory()->create([
        'word_ids' => $words->pluck('id')->toArray(),
    ]);

    Livewire\Livewire::test('holocron.school.vocabulary-test', ['test' => $test])
        ->call('markAsCorrect', $words[0]->id)
        ->call('markAsWrong', $words[1]->id);

    $test->refresh();
    expect($words[0]->fresh()->right)->toBe(1);
    expect($words[1]->fresh()->wrong)->toBe(1);
    expect($test->word_ids->count())->toBe(10);
    expect($test->correct_ids->count())->toBe(1);
    expect($test->wrong_ids->count())->toBe(1);
});

it('makes sure a wrong word can be marked as correct', function () {
    $words = VocabularyWord::factory()->count(2)->create();
    $test = VocabularyTest::factory()->create([
        'word_ids' => $words->pluck('id')->toArray(),
    ]);

    Livewire\Livewire::test('holocron.school.vocabulary-test', ['test' => $test])
        ->call('markAsWrong', $words[0]->id)
        ->call('markAsCorrect', $words[0]->id);

    $test->refresh();
    expect($words[0]->fresh()->right)->toBe(1);
    expect($words[0]->fresh()->wrong)->toBe(1);
    expect($test->word_ids->count())->toBe(2);
    expect($test->correct_ids->count())->toBe(1);
    expect($test->wrong_ids->count())->toBe(1);
});

it('knows which words are left', function () {
    $words = VocabularyWord::factory()->count(10)->create();
    $test = VocabularyTest::factory()->create([
        'word_ids' => $words->pluck('id')->toArray(),
    ]);

    $test->markAsCorrect($words[0]->id);
    $test->markAsWrong($words[1]->id);

    expect($test->leftWords())->toBeInstanceOf(Illuminate\Support\Collection::class)->toHaveCount(9);
});

it('accessible by non admins', function () {
    $user = User::factory()->create();
    $words = VocabularyWord::factory()->count(10)->create();
    $test = VocabularyTest::factory()->create([
        'word_ids' => $words->pluck('id')->toArray(),
    ]);

    $this->actingAs($user)
        ->get(route('holocron.school.vocabulary.overview', $test))
        ->assertStatus(200);
});
