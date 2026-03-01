<?php

declare(strict_types=1);

use Modules\Holocron\School\Models\VocabularyWord;

it('calculates a positive score', function () {
    $word = VocabularyWord::factory()->make(['right' => 10, 'wrong' => 3]);

    expect($word->score())->toBe(7);
});

it('calculates a negative score', function () {
    $word = VocabularyWord::factory()->make(['right' => 2, 'wrong' => 8]);

    expect($word->score())->toBe(-6);
});

it('calculates a zero score', function () {
    $word = VocabularyWord::factory()->make(['right' => 5, 'wrong' => 5]);

    expect($word->score())->toBe(0);
});

it('has a factory', function () {
    $word = VocabularyWord::factory()->create();

    expect($word)->toBeInstanceOf(VocabularyWord::class)
        ->and($word->exists)->toBeTrue()
        ->and($word->right)->toBe(0)
        ->and($word->wrong)->toBe(0);
});

it('has german and english attributes', function () {
    $word = VocabularyWord::factory()->create([
        'german' => 'Apfel',
        'english' => 'Apple',
    ]);

    expect($word->german)->toBe('Apfel')
        ->and($word->english)->toBe('Apple');
});
