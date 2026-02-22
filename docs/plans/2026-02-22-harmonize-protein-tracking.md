# Harmonize Protein Tracking Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Make nutrition targets and logged meal protein totals the canonical source for Dashboard protein goal progress.

**Architecture:** Keep `daily_goals` as the Dashboard read model for streak/progress, but project protein values from `grind_nutrition_days` + `user_settings.nutrition_daily_targets`. Sync runs inline in the nutrition domain (`NutritionDay`) so all write paths (Livewire + AI tool) stay consistent. Day-type changes trigger re-sync to refresh target values.

**Tech Stack:** Laravel 12, Livewire 4, Pest 4, Flux UI Pro

---

**Implementation Skills:** `@test-driven-development`, `@pest-testing`, `@livewire-development`, `@verification-before-completion`

### Task 1: Add failing tests for protein projection + fallback

**Files:**
- Modify: `modules/Holocron/Grind/Tests/Unit/NutritionDayTest.php`
- Modify: `tests/Feature/Ai/Tools/LogMealTest.php`

**Step 1: Write the failing tests**

In `modules/Holocron/Grind/Tests/Unit/NutritionDayTest.php`, add imports and tests:

```php
use Modules\Holocron\User\Enums\GoalType;
use Modules\Holocron\User\Models\DailyGoal;
use Modules\Holocron\User\Models\User;

it('projects nutrition protein totals into daily protein goal', function () {
    $tim = User::factory()->create(['email' => 'timkley@gmail.com']);
    $tim->settings()->create([
        'weight' => 80,
        'nutrition_daily_targets' => [
            'training' => ['kcal' => 2200, 'protein' => 155, 'fat' => 65, 'carbs' => 230],
            'rest' => ['kcal' => 2000, 'protein' => 140, 'fat' => 60, 'carbs' => 185],
        ],
    ]);

    $day = NutritionDay::factory()->create([
        'date' => today()->toDateString(),
        'type' => 'training',
        'meals' => [
            ['name' => 'Meal 1', 'kcal' => 500, 'protein' => 30, 'fat' => 20, 'carbs' => 50],
            ['name' => 'Meal 2', 'kcal' => 700, 'protein' => 40, 'fat' => 25, 'carbs' => 70],
        ],
        'total_kcal' => 0,
        'total_protein' => 0,
        'total_fat' => 0,
        'total_carbs' => 0,
    ]);

    $day->recalculateTotals();

    $goal = DailyGoal::query()
        ->where('type', GoalType::Protein)
        ->whereDate('date', $day->date)
        ->first();

    expect($goal)->not->toBeNull()
        ->and($goal->amount)->toBe(70)
        ->and($goal->goal)->toBe(155);
});

it('falls back to weight based target when day type target is missing', function () {
    $tim = User::factory()->create(['email' => 'timkley@gmail.com']);
    $tim->settings()->create([
        'weight' => 82,
        'nutrition_daily_targets' => [
            'rest' => ['kcal' => 2000, 'protein' => 140, 'fat' => 60, 'carbs' => 185],
        ],
    ]);

    $day = NutritionDay::factory()->create([
        'date' => today()->toDateString(),
        'type' => 'training',
        'meals' => [
            ['name' => 'Meal', 'kcal' => 400, 'protein' => 50, 'fat' => 12, 'carbs' => 30],
        ],
        'total_kcal' => 0,
        'total_protein' => 0,
        'total_fat' => 0,
        'total_carbs' => 0,
    ]);

    $day->recalculateTotals();

    $goal = DailyGoal::query()
        ->where('type', GoalType::Protein)
        ->whereDate('date', $day->date)
        ->first();

    expect($goal)->not->toBeNull()
        ->and($goal->goal)->toBe(164)
        ->and($goal->amount)->toBe(50);
});
```

In `tests/Feature/Ai/Tools/LogMealTest.php`, extend `beforeEach` and add test:

```php
use Modules\Holocron\User\Enums\GoalType;
use Modules\Holocron\User\Models\DailyGoal;
use Modules\Holocron\User\Models\User;

beforeEach(function () {
    $this->testDate = today()->addYear()->toDateString();

    $tim = User::factory()->create(['email' => 'timkley@gmail.com']);
    $tim->settings()->create([
        'weight' => 80,
        'nutrition_daily_targets' => [
            'training' => ['kcal' => 2200, 'protein' => 155, 'fat' => 65, 'carbs' => 230],
            'rest' => ['kcal' => 2000, 'protein' => 140, 'fat' => 60, 'carbs' => 185],
        ],
    ]);
});

it('syncs protein daily goal projection when logging a meal', function () {
    $tool = new LogMeal;

    $tool->handle(new Request([
        'date' => $this->testDate,
        'name' => 'Protein Shake',
        'kcal' => 110,
        'protein' => 23,
        'fat' => 0,
        'carbs' => 3,
        'day_type' => 'training',
    ]));

    $goal = DailyGoal::query()
        ->where('type', GoalType::Protein)
        ->whereDate('date', $this->testDate)
        ->first();

    expect($goal)->not->toBeNull()
        ->and($goal->amount)->toBe(23)
        ->and($goal->goal)->toBe(155);
});
```

**Step 2: Run tests to verify they fail**

Run:

```bash
php artisan test --compact modules/Holocron/Grind/Tests/Unit/NutritionDayTest.php tests/Feature/Ai/Tools/LogMealTest.php --filter="projects nutrition protein totals|falls back to weight based target|syncs protein daily goal projection"
```

Expected: FAIL because protein `DailyGoal` projection does not exist yet.

**Step 3: Commit failing tests**

```bash
git add modules/Holocron/Grind/Tests/Unit/NutritionDayTest.php tests/Feature/Ai/Tools/LogMealTest.php
git commit -m "test: cover protein projection sync and fallback"
```

---

### Task 2: Implement inline protein projection sync in `NutritionDay`

**Files:**
- Modify: `modules/Holocron/Grind/Models/NutritionDay.php`
- Test: `modules/Holocron/Grind/Tests/Unit/NutritionDayTest.php`
- Test: `tests/Feature/Ai/Tools/LogMealTest.php`

**Step 1: Write minimal implementation**

In `modules/Holocron/Grind/Models/NutritionDay.php`, add imports:

```php
use Carbon\CarbonImmutable;
use Modules\Holocron\User\Enums\GoalType;
use Modules\Holocron\User\Models\DailyGoal;
use Modules\Holocron\User\Models\User;
```

Update `recalculateTotals()` to sync projection after save:

```php
public function recalculateTotals(): void
{
    $this->total_kcal = (int) collect($this->meals)->sum('kcal');
    $this->total_protein = (int) collect($this->meals)->sum('protein');
    $this->total_fat = (int) collect($this->meals)->sum('fat');
    $this->total_carbs = (int) collect($this->meals)->sum('carbs');
    $this->save();

    $this->syncProteinGoalProjection();
}
```

Add inline sync helper in the same class:

```php
private function syncProteinGoalProjection(): void
{
    $user = User::query()->where('email', 'timkley@gmail.com')->first();

    if (! $user) {
        return;
    }

    $target = $user->settings?->nutrition_daily_targets[$this->type]['protein'] ?? null;

    if (! is_int($target)) {
        $weight = $user->settings?->weight;
        $target = $weight !== null ? (int) round($weight * 2) : 0;
    }

    $goal = DailyGoal::for(GoalType::Protein, CarbonImmutable::parse($this->date->toDateString()));

    $goal->update([
        'goal' => $target,
        'amount' => $this->total_protein,
    ]);
}
```

**Step 2: Run tests to verify they pass**

Run:

```bash
php artisan test --compact modules/Holocron/Grind/Tests/Unit/NutritionDayTest.php tests/Feature/Ai/Tools/LogMealTest.php --filter="projects nutrition protein totals|falls back to weight based target|syncs protein daily goal projection|recalculates totals from meals"
```

Expected: PASS.

**Step 3: Commit**

```bash
git add modules/Holocron/Grind/Models/NutritionDay.php modules/Holocron/Grind/Tests/Unit/NutritionDayTest.php tests/Feature/Ai/Tools/LogMealTest.php
git commit -m "feat: project nutrition protein totals into daily goals"
```

---

### Task 3: Re-sync protein projection when day type changes

**Files:**
- Modify: `modules/Holocron/Grind/Tests/Feature/NutritionTest.php`
- Modify: `modules/Holocron/Grind/Livewire/Nutrition/Index.php`

**Step 1: Write failing feature test**

In `modules/Holocron/Grind/Tests/Feature/NutritionTest.php`, add imports and test:

```php
use Modules\Holocron\User\Enums\GoalType;
use Modules\Holocron\User\Models\DailyGoal;

it('updates projected protein goal target when day type changes', function () {
    $tim = User::factory()
        ->has(UserSetting::factory()->state([
            'nutrition_daily_targets' => [
                'training' => ['kcal' => 2200, 'protein' => 155, 'fat' => 65, 'carbs' => 230],
                'rest' => ['kcal' => 2000, 'protein' => 140, 'fat' => 60, 'carbs' => 185],
            ],
        ]), 'settings')
        ->create(['email' => 'timkley@gmail.com']);

    actingAs($tim);

    $day = NutritionDay::factory()->create([
        'date' => today()->toDateString(),
        'type' => 'rest',
        'meals' => [
            ['name' => 'Meal', 'kcal' => 500, 'protein' => 100, 'fat' => 20, 'carbs' => 50],
        ],
        'total_kcal' => 500,
        'total_protein' => 100,
        'total_fat' => 20,
        'total_carbs' => 50,
    ]);

    $day->recalculateTotals();

    Livewire::test('holocron.grind.nutrition.index')
        ->set('dayType', 'training')
        ->assertHasNoErrors();

    $goal = DailyGoal::query()
        ->where('type', GoalType::Protein)
        ->whereDate('date', today())
        ->first();

    expect($goal)->not->toBeNull()
        ->and($goal->amount)->toBe(100)
        ->and($goal->goal)->toBe(155);
});
```

**Step 2: Run test to verify it fails**

Run:

```bash
php artisan test --compact modules/Holocron/Grind/Tests/Feature/NutritionTest.php --filter="updates projected protein goal target when day type changes"
```

Expected: FAIL because day-type updates do not re-sync projection yet.

**Step 3: Implement minimal fix**

In `modules/Holocron/Grind/Livewire/Nutrition/Index.php`, update `updatedDayType()`:

```php
public function updatedDayType(string $type): void
{
    $day = $this->getOrCreateDay();
    $day->update(['type' => $type]);

    $day->recalculateTotals();
}
```

**Step 4: Run tests to verify they pass**

Run:

```bash
php artisan test --compact modules/Holocron/Grind/Tests/Feature/NutritionTest.php --filter="can update day type|updates projected protein goal target when day type changes"
```

Expected: PASS.

**Step 5: Commit**

```bash
git add modules/Holocron/Grind/Livewire/Nutrition/Index.php modules/Holocron/Grind/Tests/Feature/NutritionTest.php
git commit -m "feat: resync protein projection on day type changes"
```

---

### Task 4: Make Dashboard protein goal read-only

**Files:**
- Modify: `modules/Holocron/User/Tests/DailyGoalTest.php`
- Modify: `modules/Holocron/Dashboard/Views/components/goals/protein.blade.php`

**Step 1: Write failing UI test**

In `modules/Holocron/User/Tests/DailyGoalTest.php`, add:

```php
it('renders protein goal without manual tracking form', function () {
    $user = User::factory()->create(['email' => 'timkley@gmail.com']);
    $user->settings()->create([
        'weight' => 80,
        'nutrition_daily_targets' => [
            'rest' => ['kcal' => 2000, 'protein' => 140, 'fat' => 60, 'carbs' => 185],
        ],
    ]);

    actingAs($user);

    Livewire::test('holocron.dashboard.components.goals')
        ->assertSee('Protein')
        ->assertDontSee('eingenommen');
});
```

**Step 2: Run test to verify it fails**

Run:

```bash
php artisan test --compact modules/Holocron/User/Tests/DailyGoalTest.php --filter="renders protein goal without manual tracking form"
```

Expected: FAIL because protein button/form is still rendered.

**Step 3: Implement minimal Blade change**

In `modules/Holocron/Dashboard/Views/components/goals/protein.blade.php`, remove manual `<form>` and replace with a small read-only text block, for example:

```blade
<flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
    Wird automatisch über Ernährung synchronisiert.
</flux:text>
```

**Step 4: Run tests to verify they pass**

Run:

```bash
php artisan test --compact modules/Holocron/User/Tests/DailyGoalTest.php --filter="renders protein goal without manual tracking form|can track a goal"
```

Expected: PASS.

**Step 5: Commit**

```bash
git add modules/Holocron/Dashboard/Views/components/goals/protein.blade.php modules/Holocron/User/Tests/DailyGoalTest.php
git commit -m "feat: make dashboard protein tracking read only"
```

---

### Task 5: Run mandatory verification and finalize

**Files:**
- Verify only: repository-wide

**Step 1: Run focused regression suite**

```bash
php artisan test --compact modules/Holocron/Grind/Tests/Unit/NutritionDayTest.php modules/Holocron/Grind/Tests/Feature/NutritionTest.php tests/Feature/Ai/Tools/LogMealTest.php modules/Holocron/User/Tests/DailyGoalTest.php
```

Expected: PASS.

**Step 2: Run mandatory full verification (project rule)**

```bash
php artisan test --compact
composer phpstan
vendor/bin/pint --dirty --format agent
```

Expected: PASS for all three commands.

**Step 3: Commit any verification-driven fixes**

```bash
git add -A
git commit -m "chore: fix verification issues for protein harmonization"
```

---

### Notes for implementation

- Keep changes YAGNI: no new service/action class for this scope.
- Keep projection idempotent: always set absolute `amount` from `total_protein`.
- Do not backfill historical dates; only sync when nutrition days are written/updated.
- If sync cannot resolve a user, fail safe (no crash) and keep tests explicit by creating Tim user fixtures.
