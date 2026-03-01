<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\School\Models\VocabularyWord;
use Modules\Holocron\User\Models\User;

it('mounts with a vocabulary word', function () {
    $word = VocabularyWord::factory()->create([
        'german' => 'Haus',
        'english' => 'House',
    ]);

    Livewire::actingAs(User::factory()->create())
        ->test('holocron.school.vocabulary-word', ['word' => $word])
        ->assertSet('german', 'Haus')
        ->assertSet('english', 'House');
});

it('updates the word when a property changes', function () {
    $word = VocabularyWord::factory()->create([
        'german' => 'Haus',
        'english' => 'House',
    ]);

    Livewire::actingAs(User::factory()->create())
        ->test('holocron.school.vocabulary-word', ['word' => $word])
        ->set('german', 'Baum')
        ->assertSet('german', 'Baum');

    expect($word->fresh()->german)->toBe('Baum');
});

it('updates the english property', function () {
    $word = VocabularyWord::factory()->create([
        'german' => 'Hund',
        'english' => 'Dog',
    ]);

    Livewire::actingAs(User::factory()->create())
        ->test('holocron.school.vocabulary-word', ['word' => $word])
        ->set('english', 'Doggy')
        ->assertSet('english', 'Doggy');

    expect($word->fresh()->english)->toBe('Doggy');
});

it('renders the vocabulary word component', function () {
    $word = VocabularyWord::factory()->create([
        'german' => 'Katze',
        'english' => 'Cat',
        'right' => 5,
        'wrong' => 2,
    ]);

    Livewire::actingAs(User::factory()->create())
        ->test('holocron.school.vocabulary-word', ['word' => $word])
        ->assertOk()
        ->assertSee('3');
});
