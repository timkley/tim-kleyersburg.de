<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Modules\Holocron\User\Enums\GoalType;
use Modules\Holocron\User\Models\DailyGoal;
use Modules\Holocron\User\Models\User;
use Modules\Holocron\User\Models\UserSetting;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Http::fake([
        'geocoding-api.open-meteo.com/*' => Http::response([
            'results' => [['latitude' => 48.8, 'longitude' => 9.27]],
        ]),
        'api.open-meteo.com/*' => Http::response([
            'daily' => [
                'time' => [today()->format('Y-m-d')],
                'weather_code' => [0],
                'temperature_2m_max' => [20.0],
                'temperature_2m_min' => [10.0],
                'precipitation_sum' => [0.0],
            ],
        ]),
    ]);

    $user = User::factory()
        ->has(UserSetting::factory(), 'settings')
        ->create(['email' => 'timkley@gmail.com']);

    actingAs($user);

    DailyGoal::query()->delete();
});

it('renders the goals component', function () {
    DailyGoal::factory()->create([
        'type' => GoalType::Water,
        'unit' => GoalType::Water->unit(),
        'goal' => 3000,
        'amount' => 1500,
        'date' => today()->format('Y-m-d'),
    ]);

    Livewire::test('holocron.dashboard.components.goals')
        ->assertSuccessful();
});

it('creates daily goals when none exist for today', function () {
    Livewire::test('holocron.dashboard.components.goals');

    expect(DailyGoal::where('date', today()->format('Y-m-d'))->count())->toBeGreaterThan(0);
});

it('does not create duplicate goals when goals already exist for today', function () {
    // Create goals for today for all active types
    foreach (GoalType::cases() as $type) {
        if ($type->deactivated()) {
            continue;
        }

        DailyGoal::factory()->create([
            'type' => $type,
            'unit' => $type->unit(),
            'goal' => 100,
            'amount' => 0,
            'date' => today()->format('Y-m-d'),
        ]);
    }

    $countBefore = DailyGoal::count();

    Livewire::test('holocron.dashboard.components.goals');

    expect(DailyGoal::count())->toBe($countBefore);
});

it('can select a different date', function () {
    DailyGoal::factory()->create([
        'type' => GoalType::Water,
        'unit' => GoalType::Water->unit(),
        'goal' => 3000,
        'amount' => 0,
        'date' => today()->format('Y-m-d'),
    ]);

    $yesterday = now()->subDay()->format('Y-m-d');

    Livewire::test('holocron.dashboard.components.goals')
        ->call('selectDate', $yesterday)
        ->assertSet('selectedDate', Carbon\CarbonImmutable::parse($yesterday));
});

it('can track a goal amount', function () {
    $goal = DailyGoal::factory()->create([
        'type' => GoalType::Water,
        'unit' => GoalType::Water->unit(),
        'goal' => 3000,
        'amount' => 0,
        'date' => today()->format('Y-m-d'),
    ]);

    Livewire::test('holocron.dashboard.components.goals')
        ->call('trackGoal', GoalType::Water->value, 500)
        ->assertHasNoErrors();

    $goal->refresh();

    expect($goal->amount)->toBe(500);
});

it('validates amount is required when tracking a goal', function () {
    DailyGoal::factory()->create([
        'type' => GoalType::Water,
        'unit' => GoalType::Water->unit(),
        'goal' => 3000,
        'amount' => 0,
        'date' => today()->format('Y-m-d'),
    ]);

    Livewire::test('holocron.dashboard.components.goals')
        ->call('trackGoal', GoalType::Water->value, null)
        ->assertHasErrors(['amount']);
});

it('validates amount must be numeric when tracking a goal', function () {
    DailyGoal::factory()->create([
        'type' => GoalType::Water,
        'unit' => GoalType::Water->unit(),
        'goal' => 3000,
        'amount' => 0,
        'date' => today()->format('Y-m-d'),
    ]);

    Livewire::test('holocron.dashboard.components.goals')
        ->call('trackGoal', GoalType::Water->value, null)
        ->assertHasErrors(['amount' => 'required']);
});

it('passes goals period data to the view', function () {
    DailyGoal::factory()->create([
        'type' => GoalType::Water,
        'unit' => GoalType::Water->unit(),
        'goal' => 3000,
        'amount' => 3000,
        'date' => today()->format('Y-m-d'),
    ]);

    DailyGoal::factory()->create([
        'type' => GoalType::Water,
        'unit' => GoalType::Water->unit(),
        'goal' => 3000,
        'amount' => 1000,
        'date' => today()->subDay()->format('Y-m-d'),
    ]);

    $component = Livewire::test('holocron.dashboard.components.goals');

    $count = $component->viewData('goalsPast20DaysCount');
    $reachedCount = $component->viewData('goalsPast20DaysReachedCount');

    expect($count)->toBe(2)
        ->and($reachedCount)->toBe(1);
});

it('passes streak data to the view', function () {
    // Create a 3-day streak of reached water goals
    foreach (range(0, 2) as $daysAgo) {
        DailyGoal::factory()->create([
            'type' => GoalType::Water,
            'unit' => GoalType::Water->unit(),
            'goal' => 3000,
            'amount' => 3000,
            'date' => today()->subDays($daysAgo)->format('Y-m-d'),
        ]);
    }

    $component = Livewire::test('holocron.dashboard.components.goals');

    $streaks = $component->viewData('streaksByType');

    expect($streaks)->toHaveKey(GoalType::Water->value)
        ->and($streaks[GoalType::Water->value]['current'])->toBe(3)
        ->and($streaks[GoalType::Water->value]['highest'])->toBe(3);
});
