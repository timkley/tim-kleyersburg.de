<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Tests\Feature\Livewire\Components;

use Livewire\Livewire;
use Modules\Holocron\_Shared\Livewire\TaskModal;
use Modules\Holocron\Quest\Models\Quest;

use function Pest\Laravel\assertDatabaseHas;

it('renders the task modal component', function () {
    Livewire::test(TaskModal::class)
        ->assertOk()
        ->assertSee('Quest anlegen');
});

it('can create a quest and redirect', function () {
    Livewire::test(TaskModal::class)
        ->set('name', 'My new quest')
        ->call('submit', false)
        ->assertRedirect(route('holocron.quests.show', Quest::first()));

    assertDatabaseHas('quests', [
        'name' => 'My new quest',
    ]);
});

it('can create a quest and stay open', function () {
    Livewire::test(TaskModal::class)
        ->set('name', 'Another quest')
        ->call('submit', true)
        ->assertSet('name', '')
        ->assertNoRedirect();

    assertDatabaseHas('quests', [
        'name' => 'Another quest',
    ]);
});

it('validates required name field', function () {
    Livewire::test(TaskModal::class)
        ->set('name', '')
        ->call('submit', false)
        ->assertHasErrors(['name' => 'required']);
});

it('can create a quest with print flag', function () {
    Livewire::test(TaskModal::class)
        ->set('name', 'Quest to print')
        ->set('should_be_printed', true)
        ->call('submit', false)
        ->assertRedirect();

    assertDatabaseHas('quests', [
        'name' => 'Quest to print',
        'should_be_printed' => true,
    ]);
});

it('can create a quest with a specific date', function () {
    $date = today()->addDay()->toDateString();

    Livewire::test(TaskModal::class)
        ->set('name', 'Quest for tomorrow')
        ->set('date', $date)
        ->call('submit', false)
        ->assertRedirect();

    assertDatabaseHas('quests', [
        'name' => 'Quest for tomorrow',
        'date' => $date,
    ]);
});

it('validates date field format', function () {
    Livewire::test(TaskModal::class)
        ->set('name', 'Test quest')
        ->set('date', 'invalid-date')
        ->call('submit', false)
        ->assertHasErrors(['date' => 'date']);
});

it('resets form after creating quest and staying open', function () {
    Livewire::test(TaskModal::class)
        ->set('name', 'Test quest')
        ->set('should_be_printed', true)
        ->set('date', today()->toDateString())
        ->call('submit', true)
        ->assertSet('name', '')
        ->assertSet('should_be_printed', false)
        ->assertSet('date', '');
});

it('dispatches quest created event when staying open', function () {
    Livewire::test(TaskModal::class)
        ->set('name', 'Test quest')
        ->call('submit', true)
        ->assertDispatched('quest:created');
});

it('does not dispatch quest created event when redirecting', function () {
    Livewire::test(TaskModal::class)
        ->set('name', 'Test quest')
        ->call('submit', false)
        ->assertNotDispatched('quest:created');
});

it('creates quest with all fields correctly', function () {
    $date = today()->addDays(2)->toDateString();

    Livewire::test(TaskModal::class)
        ->set('name', 'Complete quest')
        ->set('should_be_printed', true)
        ->set('date', $date)
        ->call('submit', false);

    $quest = Quest::first();

    expect($quest->name)->toBe('Complete quest')
        ->and($quest->should_be_printed)->toBe(1)
        ->and($quest->date->toDateString())->toBe($date);
});

it('renders both submit buttons', function () {
    Livewire::test(TaskModal::class)
        ->assertSee('Quest anlegen')
        ->assertSee('Anlegen')
        ->assertSee('neu');
});
