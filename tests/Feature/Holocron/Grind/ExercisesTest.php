<?php

declare(strict_types=1);

use App\Models\Holocron\Grind\Exercise;

use function Pest\Laravel\get;

it('is not reachable when unauthenticated', function () {
    get(route('holocron.grind.exercises.index'))
        ->assertRedirect();
});

it('works', function () {
    Livewire::test('holocron.grind.exercises.index')
        ->assertSuccessful();
});

it('can delete exercises', function () {
    Exercise::factory()->create();

    Livewire::test('holocron.grind.exercises.index')
        ->call('delete', 1);

    expect(Exercise::count())->toBe(0);
});
