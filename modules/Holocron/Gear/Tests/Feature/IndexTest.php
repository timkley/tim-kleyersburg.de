<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Gear\Enums\Property;
use Modules\Holocron\Gear\Models\Journey;
use Modules\Holocron\Gear\Models\JourneyItem;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('is not reachable when unauthenticated', function () {
    get(route('holocron.gear'))
        ->assertRedirect();
});

it('renders the gear index page', function () {
    actingAs(User::factory()->create());

    Livewire::test('holocron.gear.index')
        ->assertSuccessful();
});

it('displays only current and future journeys', function () {
    actingAs(User::factory()->create());

    $futureJourney = Journey::factory()->create([
        'destination' => 'Future Trip',
        'starts_at' => today()->addDay(),
        'ends_at' => today()->addWeek(),
    ]);

    $pastJourney = Journey::factory()->create([
        'destination' => 'Past Trip',
        'starts_at' => today()->subWeeks(2),
        'ends_at' => today()->subWeek(),
    ]);

    Livewire::test('holocron.gear.index')
        ->assertSee('Future Trip')
        ->assertDontSee('Past Trip');
});

it('can delete a journey', function () {
    actingAs(User::factory()->create());

    $journey = Journey::factory()->create();

    Livewire::test('holocron.gear.index')
        ->call('delete', $journey->id);

    expect(Journey::where('id', $journey->id)->exists())->toBeFalse();
});

it('deletes related journey items when deleting a journey', function () {
    actingAs(User::factory()->create());

    $journey = Journey::factory()->create();
    JourneyItem::factory()->count(3)->create(['journey_id' => $journey->id]);

    expect(JourneyItem::where('journey_id', $journey->id)->count())->toBe(3);

    Livewire::test('holocron.gear.index')
        ->call('delete', $journey->id);

    expect(JourneyItem::where('journey_id', $journey->id)->count())->toBe(0);
});

it('can toggle journey properties', function () {
    actingAs(User::factory()->create());

    $component = Livewire::test('holocron.gear.index');

    expect($component->get('selectedProperties'))->toBeEmpty();

    $component->call('toggleProperty', Property::ChildOnBoard->value);
    expect($component->get('selectedProperties'))->toContain(Property::ChildOnBoard);

    $component->call('toggleProperty', Property::ChildOnBoard->value);
    expect($component->get('selectedProperties'))->toBeEmpty();
});

it('can check if property is selected', function () {
    actingAs(User::factory()->create());

    $component = Livewire::test('holocron.gear.index')
        ->call('toggleProperty', Property::WarmWeather->value);

    expect($component->get('selectedProperties'))->toContain(Property::WarmWeather);
});

it('can create a journey and redirects to show page', function () {
    actingAs(User::factory()->create());

    Livewire::test('holocron.gear.index')
        ->set('destination', 'Berlin')
        ->set('starts_at', '2026-06-01')
        ->set('ends_at', '2026-06-05')
        ->set('selectedProperties', [Property::ChildOnBoard])
        ->call('submit')
        ->assertRedirect();

    expect(Journey::where('destination', 'Berlin')->exists())->toBeTrue();
});

it('validates destination is required when creating a journey', function () {
    actingAs(User::factory()->create());

    Livewire::test('holocron.gear.index')
        ->set('destination', '')
        ->set('starts_at', '2026-06-01')
        ->set('ends_at', '2026-06-05')
        ->call('submit')
        ->assertHasErrors(['destination']);
});

it('validates dates are required when creating a journey', function () {
    actingAs(User::factory()->create());

    Livewire::test('holocron.gear.index')
        ->set('destination', 'Berlin')
        ->set('starts_at', '')
        ->set('ends_at', '')
        ->call('submit')
        ->assertHasErrors(['starts_at', 'ends_at']);
});
