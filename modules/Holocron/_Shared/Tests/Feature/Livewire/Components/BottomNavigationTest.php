<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Tests\Feature\Livewire\Components;

use Livewire\Livewire;
use Modules\Holocron\_Shared\Livewire\BottomNavigation;
use Modules\Holocron\Grind\Models\Workout;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('renders the bottom navigation component', function () {
    Livewire::test(BottomNavigation::class)
        ->assertOk()
        ->assertSee('Home')
        ->assertSee('Quests')
        ->assertSee('Mehr')
        ->assertSee('Suche')
        ->assertSee('Neu');
});

it('contains navigation links', function () {
    Livewire::test(BottomNavigation::class)
        ->assertSeeHtml(route('holocron.dashboard'))
        ->assertSeeHtml(route('holocron.quests'));
});

it('contains menu items in the dropdown', function () {
    Livewire::test(BottomNavigation::class)
        ->assertSee('Grind')
        ->assertSee('Gear')
        ->assertSee('Chopper')
        ->assertSee('Scrobbles')
        ->assertSee('Druckaufträge');
});

it('does not show active workout link when no workout is in progress', function () {
    Livewire::test(BottomNavigation::class)
        ->assertDontSeeHtml('animate-rotate-wiggle');
});

it('shows active workout link when a workout is in progress', function () {
    $workout = Workout::factory()->create([
        'started_at' => now(),
        'finished_at' => null,
    ]);

    Livewire::test(BottomNavigation::class)
        ->assertSeeHtml(route('holocron.grind.workouts.show', $workout->id));
});

it('does not show workout link for finished workouts', function () {
    Workout::factory()->create([
        'started_at' => now()->subHour(),
        'finished_at' => now(),
    ]);

    Livewire::test(BottomNavigation::class)
        ->assertDontSeeHtml('animate-rotate-wiggle');
});

it('re-renders when workout:finished event is dispatched', function () {
    Livewire::test(BottomNavigation::class)
        ->dispatch('workout:finished')
        ->assertOk();
});
