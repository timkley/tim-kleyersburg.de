# Nutrition Tracking Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Add nutrition tracking (daily meals, body measurements) to the Grind module with a dedicated UI and Chopper AI tools.

**Architecture:** Extends the existing Grind module with two new models (NutritionDay with JSON meals, BodyMeasurement), two Livewire page components, two Chopper tools, and a one-time data import migration. Nutrition targets live on the existing UserSetting model.

**Tech Stack:** Laravel 12, Livewire 4, Flux UI Pro, Pest 4, Laravel AI SDK tools

---

### Task 1: Migration — Add nutrition_daily_targets to user_settings

**Files:**
- Create: `modules/Holocron/User/Database/Migrations/2026_02_21_000001_add_nutrition_daily_targets_to_user_settings_table.php`
- Modify: `modules/Holocron/User/Models/UserSetting.php`

**Step 1: Create migration**

```bash
php artisan make:migration add_nutrition_daily_targets_to_user_settings_table --path=modules/Holocron/User/Database/Migrations --no-interaction
```

Edit the migration to:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table): void {
            $table->json('nutrition_daily_targets')->nullable()->after('printer_silenced');
        });
    }
};
```

**Step 2: Add cast and property doc to UserSetting model**

In `modules/Holocron/User/Models/UserSetting.php`, add the `nutrition_daily_targets` property to the PHPDoc and add a `casts()` method:

```php
/**
 * @property-read float $weight
 * @property-read bool $printer_silenced
 * @property-read ?array $nutrition_daily_targets
 */
class UserSetting extends Model
{
    // ... existing code ...

    protected function casts(): array
    {
        return [
            'nutrition_daily_targets' => 'array',
        ];
    }
}
```

**Step 3: Run migration**

```bash
php artisan migrate
```

Expected: Migration runs successfully.

**Step 4: Run existing settings tests**

```bash
php artisan test modules/Holocron/User/Tests/SettingsTest.php
```

Expected: All pass.

**Step 5: Commit**

```
feat(grind): add nutrition_daily_targets column to user_settings
```

---

### Task 2: Migration — Create grind_nutrition_days table

**Files:**
- Create: `modules/Holocron/Grind/Database/Migrations/2026_02_21_000002_create_grind_nutrition_days_table.php`

**Step 1: Create migration**

```bash
php artisan make:migration create_grind_nutrition_days_table --path=modules/Holocron/Grind/Database/Migrations --no-interaction
```

Edit to:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grind_nutrition_days', function (Blueprint $table): void {
            $table->id();
            $table->date('date')->unique();
            $table->string('type');
            $table->string('training_label')->nullable();
            $table->json('meals');
            $table->text('notes')->nullable();
            $table->unsignedInteger('total_kcal')->default(0);
            $table->unsignedInteger('total_protein')->default(0);
            $table->unsignedInteger('total_fat')->default(0);
            $table->unsignedInteger('total_carbs')->default(0);
            $table->timestamps();
        });
    }
};
```

**Step 2: Run migration**

```bash
php artisan migrate
```

**Step 3: Commit**

```
feat(grind): create grind_nutrition_days table
```

---

### Task 3: Migration — Create grind_body_measurements table

**Files:**
- Create: `modules/Holocron/Grind/Database/Migrations/2026_02_21_000003_create_grind_body_measurements_table.php`

**Step 1: Create migration**

```bash
php artisan make:migration create_grind_body_measurements_table --path=modules/Holocron/Grind/Database/Migrations --no-interaction
```

Edit to:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grind_body_measurements', function (Blueprint $table): void {
            $table->id();
            $table->date('date')->unique();
            $table->decimal('weight', 5, 2);
            $table->decimal('body_fat', 4, 1)->nullable();
            $table->decimal('muscle_mass', 4, 1)->nullable();
            $table->unsignedInteger('visceral_fat')->nullable();
            $table->decimal('bmi', 4, 1)->nullable();
            $table->decimal('body_water', 4, 1)->nullable();
            $table->timestamps();
        });
    }
};
```

**Step 2: Run migration**

```bash
php artisan migrate
```

**Step 3: Commit**

```
feat(grind): create grind_body_measurements table
```

---

### Task 4: Models — NutritionDay and BodyMeasurement

**Files:**
- Create: `modules/Holocron/Grind/Models/NutritionDay.php`
- Create: `modules/Holocron/Grind/Models/BodyMeasurement.php`
- Create: `modules/Holocron/Grind/Database/Factories/NutritionDayFactory.php`
- Create: `modules/Holocron/Grind/Database/Factories/BodyMeasurementFactory.php`

**Step 1: Write NutritionDay model test**

Create `modules/Holocron/Grind/Tests/Unit/NutritionDayTest.php`:

```php
<?php

declare(strict_types=1);

use Modules\Holocron\Grind\Models\NutritionDay;

it('casts meals to array', function () {
    $day = NutritionDay::factory()->create();

    expect($day->meals)->toBeArray();
});

it('casts date to carbon', function () {
    $day = NutritionDay::factory()->create();

    expect($day->date)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('recalculates totals from meals', function () {
    $day = NutritionDay::factory()->create([
        'meals' => [
            ['name' => 'Meal 1', 'kcal' => 500, 'protein' => 30, 'fat' => 20, 'carbs' => 50],
            ['name' => 'Meal 2', 'kcal' => 300, 'protein' => 25, 'fat' => 10, 'carbs' => 30],
        ],
        'total_kcal' => 0,
        'total_protein' => 0,
        'total_fat' => 0,
        'total_carbs' => 0,
    ]);

    $day->recalculateTotals();

    expect($day->total_kcal)->toBe(800)
        ->and($day->total_protein)->toBe(55)
        ->and($day->total_fat)->toBe(30)
        ->and($day->total_carbs)->toBe(80);
});
```

**Step 2: Run tests, verify they fail**

```bash
php artisan test modules/Holocron/Grind/Tests/Unit/NutritionDayTest.php
```

Expected: FAIL (class not found).

**Step 3: Create NutritionDay model**

`modules/Holocron/Grind/Models/NutritionDay.php`:

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Holocron\Grind\Database\Factories\NutritionDayFactory;

/**
 * @property-read \Illuminate\Support\Carbon $date
 * @property-read string $type
 * @property-read ?string $training_label
 * @property-read array<int, array{name: string, time?: string, kcal: int, protein: int, fat: int, carbs: int}> $meals
 * @property-read ?string $notes
 * @property int $total_kcal
 * @property int $total_protein
 * @property int $total_fat
 * @property int $total_carbs
 */
class NutritionDay extends Model
{
    /** @use HasFactory<NutritionDayFactory> */
    use HasFactory;

    protected $table = 'grind_nutrition_days';

    public function recalculateTotals(): void
    {
        $this->total_kcal = (int) collect($this->meals)->sum('kcal');
        $this->total_protein = (int) collect($this->meals)->sum('protein');
        $this->total_fat = (int) collect($this->meals)->sum('fat');
        $this->total_carbs = (int) collect($this->meals)->sum('carbs');
        $this->save();
    }

    protected static function newFactory(): NutritionDayFactory
    {
        return NutritionDayFactory::new();
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'meals' => 'array',
        ];
    }
}
```

**Step 4: Create NutritionDayFactory**

`modules/Holocron/Grind/Database/Factories/NutritionDayFactory.php`:

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Grind\Models\NutritionDay;

/**
 * @extends Factory<NutritionDay>
 */
class NutritionDayFactory extends Factory
{
    protected $model = NutritionDay::class;

    public function definition(): array
    {
        return [
            'date' => fake()->unique()->date(),
            'type' => fake()->randomElement(['training', 'rest', 'sick']),
            'training_label' => null,
            'meals' => [
                ['name' => 'Frühstück', 'time' => '08:00', 'kcal' => 500, 'protein' => 30, 'fat' => 20, 'carbs' => 50],
                ['name' => 'Mittagessen', 'time' => '12:30', 'kcal' => 700, 'protein' => 40, 'fat' => 25, 'carbs' => 70],
            ],
            'total_kcal' => 1200,
            'total_protein' => 70,
            'total_fat' => 45,
            'total_carbs' => 120,
        ];
    }

    public function training(string $label = 'upper'): static
    {
        return $this->state(fn () => [
            'type' => 'training',
            'training_label' => $label,
        ]);
    }

    public function rest(): static
    {
        return $this->state(fn () => [
            'type' => 'rest',
            'training_label' => null,
        ]);
    }
}
```

**Step 5: Create BodyMeasurement model**

`modules/Holocron/Grind/Models/BodyMeasurement.php`:

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Holocron\Grind\Database\Factories\BodyMeasurementFactory;

/**
 * @property-read \Illuminate\Support\Carbon $date
 * @property-read float $weight
 * @property-read ?float $body_fat
 * @property-read ?float $muscle_mass
 * @property-read ?int $visceral_fat
 * @property-read ?float $bmi
 * @property-read ?float $body_water
 */
class BodyMeasurement extends Model
{
    /** @use HasFactory<BodyMeasurementFactory> */
    use HasFactory;

    protected $table = 'grind_body_measurements';

    protected static function newFactory(): BodyMeasurementFactory
    {
        return BodyMeasurementFactory::new();
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'weight' => 'decimal:2',
            'body_fat' => 'decimal:1',
            'muscle_mass' => 'decimal:1',
            'bmi' => 'decimal:1',
            'body_water' => 'decimal:1',
        ];
    }
}
```

**Step 6: Create BodyMeasurementFactory**

`modules/Holocron/Grind/Database/Factories/BodyMeasurementFactory.php`:

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Grind\Models\BodyMeasurement;

/**
 * @extends Factory<BodyMeasurement>
 */
class BodyMeasurementFactory extends Factory
{
    protected $model = BodyMeasurement::class;

    public function definition(): array
    {
        return [
            'date' => fake()->unique()->date(),
            'weight' => fake()->randomFloat(2, 70, 85),
            'body_fat' => fake()->randomFloat(1, 15, 25),
            'muscle_mass' => fake()->randomFloat(1, 50, 60),
            'visceral_fat' => fake()->numberBetween(4, 10),
            'bmi' => fake()->randomFloat(1, 20, 28),
            'body_water' => fake()->randomFloat(1, 50, 65),
        ];
    }
}
```

**Step 7: Run model tests**

```bash
php artisan test modules/Holocron/Grind/Tests/Unit/NutritionDayTest.php
```

Expected: All 3 pass.

**Step 8: Commit**

```
feat(grind): add NutritionDay and BodyMeasurement models with factories
```

---

### Task 5: Data Import Migration

**Files:**
- Create: `modules/Holocron/Grind/Database/Migrations/2026_02_21_000004_import_nutrition_json_data.php`
- Test: `modules/Holocron/Grind/Tests/Feature/NutritionImportTest.php`

**Step 1: Write import test**

Create `modules/Holocron/Grind/Tests/Feature/NutritionImportTest.php`:

```php
<?php

declare(strict_types=1);

use Modules\Holocron\Grind\Models\BodyMeasurement;
use Modules\Holocron\Grind\Models\NutritionDay;
use Modules\Holocron\User\Models\UserSetting;

it('imports nutrition json data via migration', function () {
    // The migration runs automatically via RefreshDatabase.
    // Verify the data was imported.

    // Check daily targets were set on user settings
    $settings = UserSetting::first();
    expect($settings->nutrition_daily_targets)->toBeArray()
        ->and($settings->nutrition_daily_targets['training']['kcal'])->toBe(2200)
        ->and($settings->nutrition_daily_targets['rest']['protein'])->toBe(155);

    // Check nutrition days were imported
    expect(NutritionDay::count())->toBeGreaterThanOrEqual(15);

    $feb01 = NutritionDay::query()->where('date', '2026-02-01')->first();
    expect($feb01)->not->toBeNull()
        ->and($feb01->type)->toBe('training')
        ->and($feb01->training_label)->toBe('lower')
        ->and($feb01->meals)->toBeArray()
        ->and(count($feb01->meals))->toBeGreaterThan(0)
        ->and($feb01->total_kcal)->toBe(2380);

    // Check body measurements were imported
    expect(BodyMeasurement::count())->toBe(3);

    $firstMeasurement = BodyMeasurement::query()->where('date', '2026-01-19')->first();
    expect($firstMeasurement)->not->toBeNull()
        ->and((float) $firstMeasurement->weight)->toBe(77.55)
        ->and((float) $firstMeasurement->body_fat)->toBe(22.2)
        ->and((float) $firstMeasurement->muscle_mass)->toBe(57.3);
});
```

**Step 2: Run test, verify it fails**

```bash
php artisan test --filter='imports nutrition json data via migration'
```

Expected: FAIL (no data imported yet).

**Step 3: Create the import migration**

`modules/Holocron/Grind/Database/Migrations/2026_02_21_000004_import_nutrition_json_data.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Modules\Holocron\Grind\Models\BodyMeasurement;
use Modules\Holocron\Grind\Models\NutritionDay;
use Modules\Holocron\User\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        $path = base_path('nutrition.json');

        if (! file_exists($path)) {
            return;
        }

        $data = json_decode(file_get_contents($path), true);

        // Import daily targets to user settings
        $user = User::first();
        if ($user && isset($data['profile']['dailyTargets'])) {
            $user->settings->update([
                'nutrition_daily_targets' => $data['profile']['dailyTargets'],
            ]);
        }

        // Import body measurements
        if (isset($data['profile']['measurements'])) {
            foreach ($data['profile']['measurements'] as $measurement) {
                BodyMeasurement::query()->updateOrCreate(
                    ['date' => $measurement['date']],
                    [
                        'weight' => $measurement['weight'],
                        'body_fat' => $measurement['bodyFat'] ?? null,
                        'muscle_mass' => $measurement['muscleMass'] ?? null,
                        'visceral_fat' => $measurement['visceralFat'] ?? null,
                        'bmi' => $measurement['bmi'] ?? null,
                        'body_water' => $measurement['bodyWater'] ?? null,
                    ],
                );
            }
        }

        // Import nutrition days
        if (isset($data['days'])) {
            foreach ($data['days'] as $date => $day) {
                NutritionDay::query()->updateOrCreate(
                    ['date' => $date],
                    [
                        'type' => $day['type'],
                        'training_label' => $day['training'] ?? null,
                        'meals' => $day['meals'] ?? [],
                        'notes' => $day['notes'] ?? null,
                        'total_kcal' => $day['totals']['kcal'] ?? 0,
                        'total_protein' => $day['totals']['protein'] ?? 0,
                        'total_fat' => $day['totals']['fat'] ?? 0,
                        'total_carbs' => $day['totals']['carbs'] ?? 0,
                    ],
                );
            }
        }
    }
};
```

**Step 4: Run test**

```bash
php artisan test --filter='imports nutrition json data via migration'
```

Expected: PASS.

**Step 5: Commit**

```
feat(grind): import nutrition.json data via migration
```

---

### Task 6: Livewire — Nutrition Day View (Ernährung)

**Files:**
- Create: `modules/Holocron/Grind/Livewire/Nutrition/Index.php`
- Create: `modules/Holocron/Grind/Views/nutrition/index.blade.php`
- Modify: `modules/Holocron/Grind/Routes/web.php` (add route)
- Modify: `modules/Holocron/Grind/Views/navigation.blade.php` (add nav item)
- Test: `modules/Holocron/Grind/Tests/Feature/NutritionTest.php`

**Step 1: Write feature test**

Create `modules/Holocron/Grind/Tests/Feature/NutritionTest.php`:

```php
<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Grind\Models\NutritionDay;
use Modules\Holocron\User\Models\User;
use Modules\Holocron\User\Models\UserSetting;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    actingAs(User::factory()->has(UserSetting::factory()->state([
        'nutrition_daily_targets' => [
            'training' => ['kcal' => 2200, 'protein' => 155, 'fat' => 65, 'carbs' => 230],
            'rest' => ['kcal' => 2000, 'protein' => 155, 'fat' => 60, 'carbs' => 185],
            'sick' => ['kcal' => 2500, 'protein' => 155, 'fat' => 75, 'carbs' => 250],
        ],
    ]), 'settings')->create());
});

it('is not reachable when unauthenticated', function () {
    auth()->logout();

    get(route('holocron.grind.nutrition.index'))
        ->assertRedirect();
});

it('renders the nutrition page', function () {
    Livewire::test('holocron.grind.nutrition.index')
        ->assertSuccessful();
});

it('shows the current date by default', function () {
    NutritionDay::factory()->create(['date' => today()]);

    Livewire::test('holocron.grind.nutrition.index')
        ->assertSet('date', today()->toDateString());
});

it('can navigate to a different date', function () {
    $yesterday = today()->subDay()->toDateString();

    NutritionDay::factory()->create(['date' => $yesterday]);

    Livewire::test('holocron.grind.nutrition.index')
        ->call('previousDay')
        ->assertSet('date', $yesterday);
});

it('can add a meal', function () {
    Livewire::test('holocron.grind.nutrition.index')
        ->set('mealName', 'Protein Shake')
        ->set('mealKcal', 110)
        ->set('mealProtein', 23)
        ->set('mealFat', 0)
        ->set('mealCarbs', 3)
        ->call('addMeal');

    $day = NutritionDay::query()->where('date', today())->first();
    expect($day)->not->toBeNull()
        ->and($day->meals)->toHaveCount(1)
        ->and($day->meals[0]['name'])->toBe('Protein Shake')
        ->and($day->total_kcal)->toBe(110);
});

it('can delete a meal', function () {
    NutritionDay::factory()->create([
        'date' => today(),
        'meals' => [
            ['name' => 'Meal 1', 'kcal' => 500, 'protein' => 30, 'fat' => 20, 'carbs' => 50],
            ['name' => 'Meal 2', 'kcal' => 300, 'protein' => 25, 'fat' => 10, 'carbs' => 30],
        ],
        'total_kcal' => 800,
        'total_protein' => 55,
        'total_fat' => 30,
        'total_carbs' => 80,
    ]);

    Livewire::test('holocron.grind.nutrition.index')
        ->call('deleteMeal', 0);

    $day = NutritionDay::query()->where('date', today())->first();
    expect($day->meals)->toHaveCount(1)
        ->and($day->meals[0]['name'])->toBe('Meal 2')
        ->and($day->total_kcal)->toBe(300);
});

it('can update day type', function () {
    NutritionDay::factory()->create(['date' => today(), 'type' => 'rest']);

    Livewire::test('holocron.grind.nutrition.index')
        ->call('setDayType', 'training');

    expect(NutritionDay::query()->where('date', today())->first()->type)->toBe('training');
});

it('calculates 7-day rolling average', function () {
    for ($i = 0; $i < 7; $i++) {
        NutritionDay::factory()->create([
            'date' => today()->subDays($i),
            'total_kcal' => 2000,
            'total_protein' => 150,
            'total_fat' => 60,
            'total_carbs' => 200,
        ]);
    }

    Livewire::test('holocron.grind.nutrition.index')
        ->assertSet('averageKcal', 2000)
        ->assertSet('averageProtein', 150)
        ->assertSet('averageFat', 60)
        ->assertSet('averageCarbs', 200);
});
```

**Step 2: Run test, verify it fails**

```bash
php artisan test modules/Holocron/Grind/Tests/Feature/NutritionTest.php
```

Expected: FAIL (component not found).

**Step 3: Create the Livewire component**

`modules/Holocron/Grind/Livewire/Nutrition/Index.php`:

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Livewire\Nutrition;

use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Grind\Models\NutritionDay;
use Modules\Holocron\User\Models\UserSetting;

#[Title('Ernährung')]
class Index extends HolocronComponent
{
    public string $date;

    public string $mealName = '';

    public string $mealTime = '';

    public int $mealKcal = 0;

    public int $mealProtein = 0;

    public int $mealFat = 0;

    public int $mealCarbs = 0;

    public int $averageKcal = 0;

    public int $averageProtein = 0;

    public int $averageFat = 0;

    public int $averageCarbs = 0;

    public function mount(): void
    {
        $this->date = today()->toDateString();
        $this->calculateAverages();
    }

    public function previousDay(): void
    {
        $this->date = Carbon::parse($this->date)->subDay()->toDateString();
        $this->calculateAverages();
    }

    public function nextDay(): void
    {
        $this->date = Carbon::parse($this->date)->addDay()->toDateString();
        $this->calculateAverages();
    }

    public function goToDate(string $date): void
    {
        $this->date = $date;
        $this->calculateAverages();
    }

    public function setDayType(string $type): void
    {
        $day = $this->getOrCreateDay();
        $day->update(['type' => $type]);
    }

    public function setTrainingLabel(string $label): void
    {
        $day = $this->getOrCreateDay();
        $day->update(['training_label' => $label ?: null]);
    }

    public function updateNotes(string $notes): void
    {
        $day = $this->getOrCreateDay();
        $day->update(['notes' => $notes ?: null]);
    }

    public function addMeal(): void
    {
        $day = $this->getOrCreateDay();

        $meal = array_filter([
            'name' => $this->mealName,
            'time' => $this->mealTime ?: null,
            'kcal' => $this->mealKcal,
            'protein' => $this->mealProtein,
            'fat' => $this->mealFat,
            'carbs' => $this->mealCarbs,
        ], fn ($value) => $value !== null);

        $meals = $day->meals;
        $meals[] = $meal;
        $day->meals = $meals;
        $day->save();
        $day->recalculateTotals();

        $this->reset('mealName', 'mealTime', 'mealKcal', 'mealProtein', 'mealFat', 'mealCarbs');
        $this->calculateAverages();
    }

    public function deleteMeal(int $index): void
    {
        $day = NutritionDay::query()->where('date', $this->date)->first();

        if (! $day) {
            return;
        }

        $meals = $day->meals;
        array_splice($meals, $index, 1);
        $day->meals = array_values($meals);
        $day->save();
        $day->recalculateTotals();
        $this->calculateAverages();
    }

    public function render(): View
    {
        $day = NutritionDay::query()->where('date', $this->date)->first();
        $targets = UserSetting::first()?->nutrition_daily_targets;
        $dayTargets = $targets[$day?->type ?? 'rest'] ?? null;

        return view('holocron-grind::nutrition.index', [
            'day' => $day,
            'targets' => $dayTargets,
        ]);
    }

    private function getOrCreateDay(): NutritionDay
    {
        return NutritionDay::query()->firstOrCreate(
            ['date' => $this->date],
            [
                'type' => 'rest',
                'meals' => [],
                'total_kcal' => 0,
                'total_protein' => 0,
                'total_fat' => 0,
                'total_carbs' => 0,
            ],
        );
    }

    private function calculateAverages(): void
    {
        $days = NutritionDay::query()
            ->where('date', '>=', Carbon::parse($this->date)->subDays(6))
            ->where('date', '<=', $this->date)
            ->get();

        $count = $days->count() ?: 1;

        $this->averageKcal = (int) round($days->sum('total_kcal') / $count);
        $this->averageProtein = (int) round($days->sum('total_protein') / $count);
        $this->averageFat = (int) round($days->sum('total_fat') / $count);
        $this->averageCarbs = (int) round($days->sum('total_carbs') / $count);
    }
}
```

**Step 4: Create the Blade view**

`modules/Holocron/Grind/Views/nutrition/index.blade.php`:

```blade
<div class="space-y-8">
    @include('holocron-grind::navigation')

    {{-- Date Navigation --}}
    <div class="flex items-center justify-center gap-4">
        <flux:button variant="ghost" icon="chevron-left" wire:click="previousDay" />
        <flux:input type="date" wire:change="goToDate($event.target.value)" :value="$date" class="w-auto" />
        <flux:button variant="ghost" icon="chevron-right" wire:click="nextDay" />
    </div>

    {{-- Day Type --}}
    <div class="flex items-center gap-4">
        <flux:radio.group :value="$day?->type ?? 'rest'" variant="segmented">
            <flux:radio value="rest" label="Rest" wire:click="setDayType('rest')" />
            <flux:radio value="training" label="Training" wire:click="setDayType('training')" />
            <flux:radio value="sick" label="Sick" wire:click="setDayType('sick')" />
        </flux:radio.group>

        @if($day?->type === 'training')
            <flux:input wire:blur="setTrainingLabel($event.target.value)" :value="$day?->training_label" placeholder="z.B. upper, lower" class="w-32" />
        @endif
    </div>

    {{-- 7-Day Averages --}}
    <div class="space-y-2">
        <flux:heading size="lg">7-Tage-Durchschnitt</flux:heading>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @php $t = $targets; @endphp
            @foreach([
                ['label' => 'kcal', 'avg' => $averageKcal, 'target' => $t['kcal'] ?? null],
                ['label' => 'Protein', 'avg' => $averageProtein, 'target' => $t['protein'] ?? null, 'unit' => 'g'],
                ['label' => 'Fett', 'avg' => $averageFat, 'target' => $t['fat'] ?? null, 'unit' => 'g'],
                ['label' => 'Carbs', 'avg' => $averageCarbs, 'target' => $t['carbs'] ?? null, 'unit' => 'g'],
            ] as $macro)
                <flux:card size="sm">
                    <flux:text>{{ $macro['label'] }}</flux:text>
                    <flux:heading class="{{ $macro['target'] && $macro['avg'] > $macro['target'] ? 'text-red-500' : 'text-green-600 dark:text-green-400' }}">
                        {{ $macro['avg'] }}{{ $macro['unit'] ?? '' }}
                    </flux:heading>
                    @if($macro['target'])
                        <flux:text class="text-xs">Ziel: {{ $macro['target'] }}{{ $macro['unit'] ?? '' }}</flux:text>
                    @endif
                </flux:card>
            @endforeach
        </div>
    </div>

    {{-- Today's Totals --}}
    @if($day)
        <div class="space-y-2">
            <flux:heading size="lg">Tagesübersicht</flux:heading>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach([
                    ['label' => 'kcal', 'value' => $day->total_kcal, 'target' => $t['kcal'] ?? null],
                    ['label' => 'Protein', 'value' => $day->total_protein, 'target' => $t['protein'] ?? null, 'unit' => 'g'],
                    ['label' => 'Fett', 'value' => $day->total_fat, 'target' => $t['fat'] ?? null, 'unit' => 'g'],
                    ['label' => 'Carbs', 'value' => $day->total_carbs, 'target' => $t['carbs'] ?? null, 'unit' => 'g'],
                ] as $macro)
                    <flux:card size="sm">
                        <flux:text>{{ $macro['label'] }}</flux:text>
                        <flux:heading>
                            {{ $macro['value'] }}{{ $macro['unit'] ?? '' }}
                            @if($macro['target'])
                                <span class="text-sm font-normal text-zinc-500"> / {{ $macro['target'] }}{{ $macro['unit'] ?? '' }}</span>
                            @endif
                        </flux:heading>
                    </flux:card>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Meals --}}
    <div class="space-y-4">
        <flux:heading size="lg">Mahlzeiten</flux:heading>

        @if($day?->meals)
            <div class="space-y-2">
                @foreach($day->meals as $index => $meal)
                    <flux:card size="sm" class="flex items-center justify-between" wire:key="meal-{{ $index }}">
                        <div>
                            <flux:heading size="sm">
                                @if(isset($meal['time']))
                                    <span class="text-zinc-500">{{ $meal['time'] }}</span>
                                @endif
                                {{ $meal['name'] }}
                            </flux:heading>
                            <flux:text class="text-xs">
                                {{ $meal['kcal'] }} kcal &middot; {{ $meal['protein'] }}g P &middot; {{ $meal['fat'] }}g F &middot; {{ $meal['carbs'] }}g C
                            </flux:text>
                        </div>
                        <flux:button variant="ghost" icon="trash" size="sm" wire:click="deleteMeal({{ $index }})" />
                    </flux:card>
                @endforeach
            </div>
        @endif

        {{-- Add Meal Form --}}
        <flux:card size="sm" class="space-y-3">
            <flux:heading size="sm">Mahlzeit hinzufügen</flux:heading>
            <div class="grid grid-cols-2 gap-3">
                <flux:input wire:model="mealName" label="Name" placeholder="z.B. Protein Shake" class="col-span-2" />
                <flux:input wire:model="mealTime" label="Uhrzeit" type="time" />
                <flux:input wire:model="mealKcal" label="kcal" type="number" />
                <flux:input wire:model="mealProtein" label="Protein (g)" type="number" />
                <flux:input wire:model="mealFat" label="Fett (g)" type="number" />
                <flux:input wire:model="mealCarbs" label="Carbs (g)" type="number" />
            </div>
            <flux:button variant="primary" wire:click="addMeal">Hinzufügen</flux:button>
        </flux:card>
    </div>

    {{-- Notes --}}
    <div class="space-y-2">
        <flux:heading size="lg">Notizen</flux:heading>
        <flux:textarea wire:blur="updateNotes($event.target.value)" :value="$day?->notes" placeholder="Notizen zum Tag..." rows="2" />
    </div>
</div>
```

**Step 5: Add route**

In `modules/Holocron/Grind/Routes/web.php`, add inside the group:

```php
Route::livewire('/nutrition', Livewire\Nutrition\Index::class)->name('grind.nutrition.index');
```

**Step 6: Add nav item**

In `modules/Holocron/Grind/Views/navigation.blade.php`, add before the closing `</flux:navbar>`:

```blade
<flux:navbar.item href="{{ route('holocron.grind.nutrition.index') }}" wire:navigate>Ernährung</flux:navbar.item>
```

**Step 7: Run tests**

```bash
php artisan test modules/Holocron/Grind/Tests/Feature/NutritionTest.php
```

Expected: All pass.

**Step 8: Run pint**

```bash
vendor/bin/pint --dirty
```

**Step 9: Commit**

```
feat(grind): add nutrition day view with meal tracking and 7-day averages
```

---

### Task 7: Livewire — Body Measurements (Körper)

**Files:**
- Create: `modules/Holocron/Grind/Livewire/Nutrition/BodyMeasurements.php`
- Create: `modules/Holocron/Grind/Views/nutrition/body-measurements.blade.php`
- Modify: `modules/Holocron/Grind/Routes/web.php` (add route)
- Modify: `modules/Holocron/Grind/Views/navigation.blade.php` (add nav item)
- Test: `modules/Holocron/Grind/Tests/Feature/BodyMeasurementsTest.php`

**Step 1: Write feature test**

Create `modules/Holocron/Grind/Tests/Feature/BodyMeasurementsTest.php`:

```php
<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Grind\Models\BodyMeasurement;
use Modules\Holocron\User\Models\User;
use Modules\Holocron\User\Models\UserSetting;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    actingAs(User::factory()->has(UserSetting::factory(), 'settings')->create());
});

it('is not reachable when unauthenticated', function () {
    auth()->logout();

    get(route('holocron.grind.body-measurements'))
        ->assertRedirect();
});

it('renders the body measurements page', function () {
    Livewire::test('holocron.grind.nutrition.body-measurements')
        ->assertSuccessful();
});

it('lists measurements newest first', function () {
    BodyMeasurement::factory()->create(['date' => '2026-01-01', 'weight' => 80.0]);
    BodyMeasurement::factory()->create(['date' => '2026-02-01', 'weight' => 78.0]);

    Livewire::test('holocron.grind.nutrition.body-measurements')
        ->assertSeeInOrder(['78', '80']);
});

it('can add a measurement', function () {
    Livewire::test('holocron.grind.nutrition.body-measurements')
        ->set('date', '2026-02-21')
        ->set('weight', 76.05)
        ->set('bodyFat', 21.4)
        ->set('muscleMass', 56.8)
        ->call('addMeasurement');

    expect(BodyMeasurement::count())->toBe(1);

    $measurement = BodyMeasurement::first();
    expect((float) $measurement->weight)->toBe(76.05)
        ->and((float) $measurement->body_fat)->toBe(21.4)
        ->and((float) $measurement->muscle_mass)->toBe(56.8);
});

it('provides chart data for weight and muscle mass', function () {
    BodyMeasurement::factory()->create(['date' => '2026-01-01', 'weight' => 80.0, 'muscle_mass' => 55.0]);
    BodyMeasurement::factory()->create(['date' => '2026-02-01', 'weight' => 78.0, 'muscle_mass' => 56.0]);

    $component = Livewire::test('holocron.grind.nutrition.body-measurements');

    $chartData = $component->viewData('chartData');
    expect($chartData)->toHaveCount(2)
        ->and($chartData[0]['weight'])->toBe(80.0)
        ->and($chartData[1]['muscle_mass'])->toBe(56.0);
});
```

**Step 2: Run test, verify it fails**

```bash
php artisan test modules/Holocron/Grind/Tests/Feature/BodyMeasurementsTest.php
```

Expected: FAIL.

**Step 3: Create the Livewire component**

`modules/Holocron/Grind/Livewire/Nutrition/BodyMeasurements.php`:

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Livewire\Nutrition;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Grind\Models\BodyMeasurement;

#[Title('Körper')]
class BodyMeasurements extends HolocronComponent
{
    public string $date = '';

    public ?float $weight = null;

    public ?float $bodyFat = null;

    public ?float $muscleMass = null;

    public ?int $visceralFat = null;

    public ?float $bmi = null;

    public ?float $bodyWater = null;

    public function mount(): void
    {
        $this->date = today()->toDateString();
    }

    public function addMeasurement(): void
    {
        BodyMeasurement::query()->updateOrCreate(
            ['date' => $this->date],
            [
                'weight' => $this->weight,
                'body_fat' => $this->bodyFat,
                'muscle_mass' => $this->muscleMass,
                'visceral_fat' => $this->visceralFat,
                'bmi' => $this->bmi,
                'body_water' => $this->bodyWater,
            ],
        );

        $this->reset('weight', 'bodyFat', 'muscleMass', 'visceralFat', 'bmi', 'bodyWater');
    }

    public function render(): View
    {
        $measurements = BodyMeasurement::query()->latest('date')->get();

        $chartData = $measurements->sortBy('date')->values()->map(fn (BodyMeasurement $m) => [
            'date' => $m->date->format('Y-m-d'),
            'weight' => (float) $m->weight,
            'muscle_mass' => $m->muscle_mass ? (float) $m->muscle_mass : null,
        ])->toArray();

        return view('holocron-grind::nutrition.body-measurements', [
            'measurements' => $measurements,
            'chartData' => $chartData,
        ]);
    }
}
```

**Step 4: Create the Blade view**

`modules/Holocron/Grind/Views/nutrition/body-measurements.blade.php`:

```blade
<div class="space-y-8">
    @include('holocron-grind::navigation')

    {{-- Charts --}}
    @if(count($chartData) >= 2)
        <div class="space-y-4">
            <flux:heading size="lg">Gewicht</flux:heading>
            <flux:chart :value="$chartData" class="aspect-[3/1]">
                <flux:chart.svg>
                    <flux:chart.cursor />
                    <flux:chart.line field="weight" class="text-sky-600" />
                    <flux:chart.axis axis="x" field="date" tick-count="10">
                        <flux:chart.axis.mark />
                        <flux:chart.axis.tick />
                        <flux:chart.axis.line />
                    </flux:chart.axis>
                    <flux:chart.axis axis="y" field="weight" tick-start="min" :format="['style' => 'unit', 'unit' => 'kilogram']">
                        <flux:chart.axis.grid />
                        <flux:chart.axis.tick />
                    </flux:chart.axis>
                </flux:chart.svg>
                <flux:chart.tooltip>
                    <flux:chart.tooltip.heading field="date" />
                    <flux:chart.tooltip.value field="weight" label="Gewicht" />
                </flux:chart.tooltip>
            </flux:chart>
        </div>

        <div class="space-y-4">
            <flux:heading size="lg">Muskelmasse</flux:heading>
            <flux:chart :value="collect($chartData)->filter(fn ($d) => $d['muscle_mass'] !== null)->values()->toArray()" class="aspect-[3/1]">
                <flux:chart.svg>
                    <flux:chart.cursor />
                    <flux:chart.line field="muscle_mass" class="text-emerald-600" />
                    <flux:chart.axis axis="x" field="date" tick-count="10">
                        <flux:chart.axis.mark />
                        <flux:chart.axis.tick />
                        <flux:chart.axis.line />
                    </flux:chart.axis>
                    <flux:chart.axis axis="y" field="muscle_mass" tick-start="min" :format="['style' => 'unit', 'unit' => 'kilogram']">
                        <flux:chart.axis.grid />
                        <flux:chart.axis.tick />
                    </flux:chart.axis>
                </flux:chart.svg>
                <flux:chart.tooltip>
                    <flux:chart.tooltip.heading field="date" />
                    <flux:chart.tooltip.value field="muscle_mass" label="Muskelmasse" />
                </flux:chart.tooltip>
            </flux:chart>
        </div>
    @endif

    {{-- Add Measurement Form --}}
    <flux:card size="sm" class="space-y-3">
        <flux:heading size="sm">Messung hinzufügen</flux:heading>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <flux:input wire:model="date" label="Datum" type="date" />
            <flux:input wire:model="weight" label="Gewicht (kg)" type="number" step="0.01" />
            <flux:input wire:model="bodyFat" label="Körperfett (%)" type="number" step="0.1" />
            <flux:input wire:model="muscleMass" label="Muskelmasse (kg)" type="number" step="0.1" />
            <flux:input wire:model="visceralFat" label="Viszeralfett" type="number" />
            <flux:input wire:model="bmi" label="BMI" type="number" step="0.1" />
            <flux:input wire:model="bodyWater" label="Körperwasser (%)" type="number" step="0.1" />
        </div>
        <flux:button variant="primary" wire:click="addMeasurement">Speichern</flux:button>
    </flux:card>

    {{-- Measurements Table --}}
    <div class="space-y-4">
        <flux:heading size="lg">Messungen</flux:heading>

        @if($measurements->isEmpty())
            <flux:text>Noch keine Messungen vorhanden.</flux:text>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Datum</flux:table.column>
                    <flux:table.column>Gewicht</flux:table.column>
                    <flux:table.column>Körperfett</flux:table.column>
                    <flux:table.column>Muskelmasse</flux:table.column>
                    <flux:table.column>BMI</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($measurements as $measurement)
                        <flux:table.row wire:key="measurement-{{ $measurement->id }}">
                            <flux:table.cell>{{ $measurement->date->format('d.m.Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $measurement->weight }} kg</flux:table.cell>
                            <flux:table.cell>{{ $measurement->body_fat ? $measurement->body_fat . '%' : '–' }}</flux:table.cell>
                            <flux:table.cell>{{ $measurement->muscle_mass ? $measurement->muscle_mass . ' kg' : '–' }}</flux:table.cell>
                            <flux:table.cell>{{ $measurement->bmi ?? '–' }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</div>
```

**Step 5: Add route**

In `modules/Holocron/Grind/Routes/web.php`, add inside the group:

```php
Route::livewire('/body-measurements', Livewire\Nutrition\BodyMeasurements::class)->name('grind.body-measurements');
```

**Step 6: Add nav item**

In `modules/Holocron/Grind/Views/navigation.blade.php`, add after the Ernährung item:

```blade
<flux:navbar.item href="{{ route('holocron.grind.body-measurements') }}" wire:navigate>Körper</flux:navbar.item>
```

**Step 7: Run tests**

```bash
php artisan test modules/Holocron/Grind/Tests/Feature/BodyMeasurementsTest.php
```

Expected: All pass.

**Step 8: Run pint**

```bash
vendor/bin/pint --dirty
```

**Step 9: Commit**

```
feat(grind): add body measurements page with weight and muscle mass charts
```

---

### Task 8: Chopper Tool — LogMeal

**Files:**
- Create: `app/Ai/Tools/LogMeal.php`
- Modify: `app/Ai/Agents/ChopperAgent.php` (register tool)
- Test: `tests/Feature/Ai/Tools/LogMealTest.php`

**Step 1: Write test**

Create `tests/Feature/Ai/Tools/LogMealTest.php`:

```php
<?php

declare(strict_types=1);

use App\Ai\Tools\LogMeal;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Grind\Models\NutritionDay;

it('logs a meal to a new day', function () {
    $tool = new LogMeal;

    $request = new Request([
        'date' => today()->toDateString(),
        'name' => 'Protein Shake',
        'kcal' => 110,
        'protein' => 23,
        'fat' => 0,
        'carbs' => 3,
    ]);

    $result = $tool->handle($request);

    expect($result)->toContain('Protein Shake');

    $day = NutritionDay::query()->where('date', today())->first();
    expect($day)->not->toBeNull()
        ->and($day->meals)->toHaveCount(1)
        ->and($day->total_kcal)->toBe(110);
});

it('appends a meal to an existing day', function () {
    NutritionDay::factory()->create([
        'date' => today(),
        'meals' => [['name' => 'Existing', 'kcal' => 500, 'protein' => 30, 'fat' => 20, 'carbs' => 50]],
        'total_kcal' => 500,
        'total_protein' => 30,
        'total_fat' => 20,
        'total_carbs' => 50,
    ]);

    $tool = new LogMeal;

    $request = new Request([
        'date' => today()->toDateString(),
        'name' => 'Shake',
        'kcal' => 110,
        'protein' => 23,
        'fat' => 0,
        'carbs' => 3,
    ]);

    $tool->handle($request);

    $day = NutritionDay::query()->where('date', today())->first();
    expect($day->meals)->toHaveCount(2)
        ->and($day->total_kcal)->toBe(610);
});

it('sets day type when creating a new day', function () {
    $tool = new LogMeal;

    $request = new Request([
        'date' => today()->toDateString(),
        'name' => 'Meal',
        'kcal' => 500,
        'protein' => 30,
        'fat' => 20,
        'carbs' => 50,
        'day_type' => 'training',
    ]);

    $tool->handle($request);

    expect(NutritionDay::query()->where('date', today())->first()->type)->toBe('training');
});
```

**Step 2: Run test, verify it fails**

```bash
php artisan test tests/Feature/Ai/Tools/LogMealTest.php
```

Expected: FAIL.

**Step 3: Create LogMeal tool**

`app/Ai/Tools/LogMeal.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Grind\Models\NutritionDay;
use Stringable;

class LogMeal implements Tool
{
    public function description(): Stringable|string
    {
        return 'Log a meal to a nutrition day. Creates the day if it does not exist. Returns confirmation with updated daily totals.';
    }

    public function handle(Request $request): Stringable|string
    {
        $day = NutritionDay::query()->firstOrCreate(
            ['date' => $request['date']],
            [
                'type' => $request['day_type'] ?? 'rest',
                'meals' => [],
                'total_kcal' => 0,
                'total_protein' => 0,
                'total_fat' => 0,
                'total_carbs' => 0,
            ],
        );

        $meal = array_filter([
            'name' => $request['name'],
            'time' => $request['time'] ?? null,
            'kcal' => $request['kcal'],
            'protein' => $request['protein'],
            'fat' => $request['fat'],
            'carbs' => $request['carbs'],
        ], fn ($value) => $value !== null);

        $meals = $day->meals;
        $meals[] = $meal;
        $day->meals = $meals;
        $day->save();
        $day->recalculateTotals();

        return sprintf(
            'Meal "%s" logged for %s. Daily totals: %d kcal, %dg protein, %dg fat, %dg carbs (%d meals total).',
            $request['name'],
            $request['date'],
            $day->total_kcal,
            $day->total_protein,
            $day->total_fat,
            $day->total_carbs,
            count($day->meals),
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'date' => $schema->string()->required(),
            'name' => $schema->string()->required(),
            'kcal' => $schema->integer()->required(),
            'protein' => $schema->integer()->required(),
            'fat' => $schema->integer()->required(),
            'carbs' => $schema->integer()->required(),
            'time' => $schema->string(),
            'day_type' => $schema->string(),
        ];
    }
}
```

**Step 4: Register in ChopperAgent**

In `app/Ai/Agents/ChopperAgent.php`, add to imports:

```php
use App\Ai\Tools\LogMeal;
```

Add to the `tools()` array:

```php
new LogMeal,
```

**Step 5: Run tests**

```bash
php artisan test tests/Feature/Ai/Tools/LogMealTest.php
```

Expected: All pass.

**Step 6: Run pint**

```bash
vendor/bin/pint --dirty
```

**Step 7: Commit**

```
feat(chopper): add LogMeal tool for nutrition tracking via chat
```

---

### Task 9: Chopper Tool — QueryNutrition

**Files:**
- Create: `app/Ai/Tools/QueryNutrition.php`
- Modify: `app/Ai/Agents/ChopperAgent.php` (register tool)
- Test: `tests/Feature/Ai/Tools/QueryNutritionTest.php`

**Step 1: Write test**

Create `tests/Feature/Ai/Tools/QueryNutritionTest.php`:

```php
<?php

declare(strict_types=1);

use App\Ai\Tools\QueryNutrition;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Grind\Models\NutritionDay;
use Modules\Holocron\User\Models\User;
use Modules\Holocron\User\Models\UserSetting;

beforeEach(function () {
    $user = User::factory()->has(UserSetting::factory()->state([
        'nutrition_daily_targets' => [
            'training' => ['kcal' => 2200, 'protein' => 155, 'fat' => 65, 'carbs' => 230],
            'rest' => ['kcal' => 2000, 'protein' => 155, 'fat' => 60, 'carbs' => 185],
        ],
    ]), 'settings')->create();

    $this->actingAs($user);
});

it('returns today data', function () {
    NutritionDay::factory()->create([
        'date' => today(),
        'type' => 'training',
        'meals' => [['name' => 'Shake', 'kcal' => 110, 'protein' => 23, 'fat' => 0, 'carbs' => 3]],
        'total_kcal' => 110,
    ]);

    $tool = new QueryNutrition;
    $result = $tool->handle(new Request(['query_type' => 'today']));

    expect($result)->toContain('Shake')
        ->and($result)->toContain('110');
});

it('returns no data message for empty day', function () {
    $tool = new QueryNutrition;
    $result = $tool->handle(new Request(['query_type' => 'today']));

    expect($result)->toContain('No nutrition data');
});

it('returns weekly summary', function () {
    for ($i = 0; $i < 3; $i++) {
        NutritionDay::factory()->create([
            'date' => today()->subDays($i),
            'total_kcal' => 2000,
        ]);
    }

    $tool = new QueryNutrition;
    $result = $tool->handle(new Request(['query_type' => 'week']));

    expect($result)->toContain('2000');
});

it('returns 7-day averages with targets', function () {
    for ($i = 0; $i < 7; $i++) {
        NutritionDay::factory()->create([
            'date' => today()->subDays($i),
            'type' => 'training',
            'total_kcal' => 2100,
            'total_protein' => 150,
            'total_fat' => 60,
            'total_carbs' => 220,
        ]);
    }

    $tool = new QueryNutrition;
    $result = $tool->handle(new Request(['query_type' => 'average']));

    expect($result)->toContain('2100')
        ->and($result)->toContain('target');
});
```

**Step 2: Run test, verify it fails**

```bash
php artisan test tests/Feature/Ai/Tools/QueryNutritionTest.php
```

Expected: FAIL.

**Step 3: Create QueryNutrition tool**

`app/Ai/Tools/QueryNutrition.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Grind\Models\NutritionDay;
use Modules\Holocron\User\Models\UserSetting;
use Stringable;

class QueryNutrition implements Tool
{
    public function description(): Stringable|string
    {
        return 'Query nutrition data. Use query_type: "today" for today\'s meals and totals, "date" for a specific date, "week" for last 7 days overview, "average" for 7-day rolling averages vs targets.';
    }

    public function handle(Request $request): Stringable|string
    {
        return match ($request['query_type']) {
            'today' => $this->queryDate(today()->toDateString()),
            'date' => $this->queryDate($request['date'] ?? today()->toDateString()),
            'week' => $this->queryWeek(),
            'average' => $this->queryAverage(),
            default => 'Unknown query type. Use: today, date, week, or average.',
        };
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query_type' => $schema->string()->required(),
            'date' => $schema->string(),
        ];
    }

    private function queryDate(string $date): string
    {
        $day = NutritionDay::query()->where('date', $date)->first();

        if (! $day) {
            return "No nutrition data for $date.";
        }

        $targets = UserSetting::first()?->nutrition_daily_targets[$day->type] ?? null;

        $meals = collect($day->meals)->map(fn (array $meal) => sprintf(
            '- %s%s: %d kcal, %dg P, %dg F, %dg C',
            isset($meal['time']) ? $meal['time'] . ' ' : '',
            $meal['name'],
            $meal['kcal'],
            $meal['protein'],
            $meal['fat'],
            $meal['carbs'],
        ))->implode("\n");

        $result = sprintf(
            "Date: %s | Type: %s%s\n\nMeals:\n%s\n\nTotals: %d kcal, %dg protein, %dg fat, %dg carbs",
            $date,
            $day->type,
            $day->training_label ? " ({$day->training_label})" : '',
            $meals,
            $day->total_kcal,
            $day->total_protein,
            $day->total_fat,
            $day->total_carbs,
        );

        if ($targets) {
            $result .= sprintf(
                "\nTargets: %d kcal, %dg protein, %dg fat, %dg carbs",
                $targets['kcal'],
                $targets['protein'],
                $targets['fat'],
                $targets['carbs'],
            );
        }

        return $result;
    }

    private function queryWeek(): string
    {
        $days = NutritionDay::query()
            ->where('date', '>=', today()->subDays(6))
            ->where('date', '<=', today())
            ->orderBy('date')
            ->get();

        if ($days->isEmpty()) {
            return 'No nutrition data for the last 7 days.';
        }

        return $days->map(fn (NutritionDay $day) => sprintf(
            '%s (%s): %d kcal, %dg P, %dg F, %dg C',
            $day->date->format('Y-m-d'),
            $day->type,
            $day->total_kcal,
            $day->total_protein,
            $day->total_fat,
            $day->total_carbs,
        ))->implode("\n");
    }

    private function queryAverage(): string
    {
        $days = NutritionDay::query()
            ->where('date', '>=', today()->subDays(6))
            ->where('date', '<=', today())
            ->get();

        if ($days->isEmpty()) {
            return 'No nutrition data for the last 7 days to average.';
        }

        $count = $days->count();
        $avgKcal = (int) round($days->sum('total_kcal') / $count);
        $avgProtein = (int) round($days->sum('total_protein') / $count);
        $avgFat = (int) round($days->sum('total_fat') / $count);
        $avgCarbs = (int) round($days->sum('total_carbs') / $count);

        $targets = UserSetting::first()?->nutrition_daily_targets;
        $result = sprintf(
            "7-day average (%d days): %d kcal, %dg protein, %dg fat, %dg carbs",
            $count,
            $avgKcal,
            $avgProtein,
            $avgFat,
            $avgCarbs,
        );

        if ($targets) {
            $result .= "\n\nTargets per day type:";
            foreach ($targets as $type => $t) {
                $result .= sprintf(
                    "\n  %s: %d kcal, %dg protein (target), %dg fat, %dg carbs",
                    $type,
                    $t['kcal'],
                    $t['protein'],
                    $t['fat'],
                    $t['carbs'],
                );
            }
        }

        return $result;
    }
}
```

**Step 4: Register in ChopperAgent**

In `app/Ai/Agents/ChopperAgent.php`, add to imports:

```php
use App\Ai\Tools\QueryNutrition;
```

Add to the `tools()` array:

```php
new QueryNutrition,
```

Also update the agent instructions to mention nutrition capabilities. Add to the instructions string:

```
Du kannst auch Ernährungsdaten tracken und abfragen. Du kannst Mahlzeiten loggen und Ernährungsübersichten abrufen.
```

**Step 5: Run tests**

```bash
php artisan test tests/Feature/Ai/Tools/QueryNutritionTest.php
```

Expected: All pass.

**Step 6: Run pint**

```bash
vendor/bin/pint --dirty
```

**Step 7: Commit**

```
feat(chopper): add QueryNutrition tool for nutrition data queries
```

---

### Task 10: Delete nutrition.json and run full test suite

**Step 1: Delete the JSON file**

```bash
rm nutrition.json
```

**Step 2: Run pint on all changed files**

```bash
vendor/bin/pint --dirty
```

**Step 3: Run the full test suite**

```bash
php artisan test
```

Expected: All tests pass.

**Step 4: Commit**

```
chore: remove nutrition.json after data import
```
