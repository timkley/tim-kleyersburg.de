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

it('works', function () {
    $user = User::factory()->create();
    actingAs($user);

    Livewire::test('holocron.gear.index')
        ->assertSuccessful();
});

it('can show all categories', function () {
    $user = User::factory()->create();
    actingAs($user);

    Livewire::test('holocron.gear.categories.index')
        ->assertSuccessful();
});

it('can create journey with properties', function () {
    $user = User::factory()->create();
    actingAs($user);

    Livewire::test('holocron.gear.index')
        ->set('destination', 'Test Destination')
        ->set('starts_at', '2025-12-01')
        ->set('ends_at', '2025-12-05')
        ->set('selectedProperties', [Property::ChildOnBoard])
        ->call('submit')
        ->assertRedirect();

    expect(Journey::count())->toBe(1);

    $journey = Journey::first();
    expect($journey->properties)->toContain(Property::ChildOnBoard);
});

it('can toggle journey properties', function () {
    $user = User::factory()->create();
    actingAs($user);

    $component = Livewire::test('holocron.gear.index');

    expect($component->get('selectedProperties'))->toBeEmpty();

    $component->call('toggleProperty', Property::ChildOnBoard->value);
    expect($component->get('selectedProperties'))->toContain(Property::ChildOnBoard);

    $component->call('toggleProperty', Property::ChildOnBoard->value);
    expect($component->get('selectedProperties'))->toBeEmpty();
});

it('can check if property is selected', function () {
    $user = User::factory()->create();
    actingAs($user);

    $component = Livewire::test('holocron.gear.index')
        ->set('selectedProperties', [Property::ChildOnBoard]);

    // Check that the property is in the selected properties array
    expect($component->get('selectedProperties'))->toContain(Property::ChildOnBoard);
});

it('property enum correctly identifies journey applicable properties', function () {
    expect(Property::ChildOnBoard->isJourneyApplicable())->toBeTrue();
    expect(Property::WarmWeather->isJourneyApplicable())->toBeFalse();
    expect(Property::CoolWeather->isJourneyApplicable())->toBeFalse();
    expect(Property::RainExpected->isJourneyApplicable())->toBeFalse();
});

it('child on board property meets condition correctly', function () {
    $journeyWithChild = Journey::factory()->create(['properties' => collect([Property::ChildOnBoard])]);
    $journeyWithoutChild = Journey::factory()->create(['properties' => collect([])]);

    expect(Property::ChildOnBoard->meetsCondition($journeyWithChild))->toBeTrue();
    expect(Property::ChildOnBoard->meetsCondition($journeyWithoutChild))->toBeFalse();
});

it('can delete a journey', function () {
    $user = User::factory()->create();
    actingAs($user);

    $journey = Journey::factory()->create();

    Livewire::test('holocron.gear.index')
        ->call('delete', $journey->id);

    expect(Journey::where('id', $journey->id)->exists())->toBeFalse();
});

it('deletes all related journey items when deleting a journey', function () {
    $user = User::factory()->create();
    actingAs($user);

    $journey = Journey::factory()->create();
    $journeyItem1 = JourneyItem::factory()->create(['journey_id' => $journey->id]);
    $journeyItem2 = JourneyItem::factory()->create(['journey_id' => $journey->id]);

    expect(JourneyItem::where('journey_id', $journey->id)->count())->toBe(2);

    Livewire::test('holocron.gear.index')
        ->call('delete', $journey->id);

    expect(Journey::where('id', $journey->id)->exists())->toBeFalse();
    expect(JourneyItem::where('journey_id', $journey->id)->count())->toBe(0);
});
