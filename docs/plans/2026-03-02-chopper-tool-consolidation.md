# Chopper Tool Consolidation Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Replace Chopper's 17 specialized tools with 3 general-purpose tools (DatabaseTool, EvalTool, FilesystemTool), normalize the meals JSON column into its own table, and add a MealObserver for protein goal syncing.

**Architecture:** Three tools replace all domain-specific tools. DatabaseTool handles raw SQL (SELECT/INSERT/UPDATE), EvalTool runs allowlisted PHP for Scout search and calculations, FilesystemTool consolidates knowledge base file operations. Schema context is auto-injected into the system prompt via a SchemaProvider. Meals are normalized from JSON into a `grind_meals` table.

**Tech Stack:** Laravel 12, laravel/ai v0, Pest 4, SQLite

---

### Task 1: Create the Meal model, migration, and factory

**Files:**
- Create: `modules/Holocron/Grind/Models/Meal.php`
- Create: `modules/Holocron/Grind/Database/Migrations/2026_03_02_000001_create_grind_meals_table.php`
- Create: `modules/Holocron/Grind/Database/Factories/MealFactory.php`
- Modify: `modules/Holocron/Grind/Models/NutritionDay.php`
- Test: `modules/Holocron/Grind/Tests/MealTest.php`

**Step 1: Write the failing test**

Create `modules/Holocron/Grind/Tests/MealTest.php`:

```php
<?php

declare(strict_types=1);

use Modules\Holocron\Grind\Models\Meal;
use Modules\Holocron\Grind\Models\NutritionDay;

it('belongs to a nutrition day', function () {
    $day = NutritionDay::factory()->create();
    $meal = Meal::factory()->create(['nutrition_day_id' => $day->id]);

    expect($meal->nutritionDay)->toBeInstanceOf(NutritionDay::class)
        ->and($meal->nutritionDay->id)->toBe($day->id);
});

it('can be created with factory defaults', function () {
    $meal = Meal::factory()->create();

    expect($meal)
        ->name->toBeString()
        ->kcal->toBeInt()
        ->protein->toBeInt()
        ->fat->toBeInt()
        ->carbs->toBeInt();
});

it('has a nutrition day with many meals', function () {
    $day = NutritionDay::factory()->create();
    Meal::factory()->count(3)->create(['nutrition_day_id' => $day->id]);

    expect($day->meals)->toHaveCount(3);
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=MealTest`
Expected: FAIL (class/table/factory don't exist yet)

**Step 3: Create migration**

Run: `php artisan make:migration create_grind_meals_table --no-interaction`

Then replace contents with:

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
        Schema::create('grind_meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nutrition_day_id')->constrained('grind_nutrition_days')->cascadeOnDelete();
            $table->string('name');
            $table->string('time')->nullable();
            $table->unsignedInteger('kcal');
            $table->unsignedInteger('protein');
            $table->unsignedInteger('fat');
            $table->unsignedInteger('carbs');
            $table->timestamps();
        });
    }
};
```

Move the migration to `modules/Holocron/Grind/Database/Migrations/` and rename with the correct timestamp prefix.

**Step 4: Create the Meal model**

Create `modules/Holocron/Grind/Models/Meal.php`:

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Holocron\Grind\Database\Factories\MealFactory;

/**
 * @property-read int $id
 * @property-read int $nutrition_day_id
 * @property-read string $name
 * @property-read ?string $time
 * @property-read int $kcal
 * @property-read int $protein
 * @property-read int $fat
 * @property-read int $carbs
 */
class Meal extends Model
{
    /** @use HasFactory<MealFactory> */
    use HasFactory;

    protected $table = 'grind_meals';

    /**
     * @return BelongsTo<NutritionDay, $this>
     */
    public function nutritionDay(): BelongsTo
    {
        return $this->belongsTo(NutritionDay::class);
    }

    protected static function newFactory(): MealFactory
    {
        return MealFactory::new();
    }
}
```

**Step 5: Create the MealFactory**

Create `modules/Holocron/Grind/Database/Factories/MealFactory.php`:

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Grind\Models\Meal;
use Modules\Holocron\Grind\Models\NutritionDay;

/**
 * @extends Factory<Meal>
 */
class MealFactory extends Factory
{
    protected $model = Meal::class;

    public function definition(): array
    {
        return [
            'nutrition_day_id' => NutritionDay::factory(),
            'name' => fake()->randomElement(['Frühstück', 'Mittagessen', 'Abendessen', 'Snack', 'Protein Shake']),
            'time' => fake()->optional()->time('H:i'),
            'kcal' => fake()->numberBetween(100, 900),
            'protein' => fake()->numberBetween(5, 60),
            'fat' => fake()->numberBetween(2, 40),
            'carbs' => fake()->numberBetween(10, 100),
        ];
    }
}
```

**Step 6: Add hasMany relationship to NutritionDay**

In `modules/Holocron/Grind/Models/NutritionDay.php`, add:

```php
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @return HasMany<Meal, $this>
 */
public function meals(): HasMany
{
    return $this->hasMany(Meal::class);
}
```

Note: The existing `$meals` property (JSON cast) will conflict with this relationship name. The `meals` JSON property will be removed in Task 2. For now, rename the relationship to `meals()` — the JSON column will be removed shortly. Temporarily, the relationship can coexist since we haven't removed the JSON column yet; however, tests should use the relationship, not the JSON property.

**Step 7: Run tests**

Run: `php artisan test --compact --filter=MealTest`
Expected: PASS

**Step 8: Run verification**

Run: `php artisan test --compact && composer phpstan && vendor/bin/pint --dirty --format agent`

**Step 9: Commit**

```bash
git add modules/Holocron/Grind/Models/Meal.php \
       modules/Holocron/Grind/Database/Migrations/*create_grind_meals_table* \
       modules/Holocron/Grind/Database/Factories/MealFactory.php \
       modules/Holocron/Grind/Models/NutritionDay.php \
       modules/Holocron/Grind/Tests/MealTest.php
git commit -m "feat: add Meal model with migration and factory"
```

---

### Task 2: Migrate meals JSON data and remove old columns

**Files:**
- Create: `modules/Holocron/Grind/Database/Migrations/2026_03_02_000002_migrate_meals_json_to_grind_meals_table.php`
- Create: `modules/Holocron/Grind/Database/Migrations/2026_03_02_000003_drop_meal_columns_from_grind_nutrition_days.php`
- Modify: `modules/Holocron/Grind/Models/NutritionDay.php` (remove meals/totals properties and casts)
- Modify: `modules/Holocron/Grind/Database/Factories/NutritionDayFactory.php` (remove meals/totals)
- Test: `modules/Holocron/Grind/Tests/MealMigrationTest.php`

**Step 1: Write the data migration**

Create a migration that reads existing JSON meals and inserts them as rows into `grind_meals`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $days = DB::select('SELECT id, meals FROM grind_nutrition_days WHERE meals IS NOT NULL');

        foreach ($days as $day) {
            $meals = json_decode($day->meals, true);

            if (! is_array($meals)) {
                continue;
            }

            foreach ($meals as $meal) {
                DB::table('grind_meals')->insert([
                    'nutrition_day_id' => $day->id,
                    'name' => $meal['name'] ?? 'Unknown',
                    'time' => $meal['time'] ?? null,
                    'kcal' => $meal['kcal'] ?? 0,
                    'protein' => $meal['protein'] ?? 0,
                    'fat' => $meal['fat'] ?? 0,
                    'carbs' => $meal['carbs'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
```

Move to `modules/Holocron/Grind/Database/Migrations/`.

**Step 2: Create migration to drop old columns**

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
        Schema::table('grind_nutrition_days', function (Blueprint $table) {
            $table->dropColumn(['meals', 'total_kcal', 'total_protein', 'total_fat', 'total_carbs']);
        });
    }
};
```

Move to `modules/Holocron/Grind/Database/Migrations/`.

**Step 3: Update NutritionDay model**

In `modules/Holocron/Grind/Models/NutritionDay.php`:

- Remove `$meals` property from PHPDoc
- Remove `$total_kcal`, `$total_protein`, `$total_fat`, `$total_carbs` from PHPDoc
- Remove `'meals' => 'array'` from `casts()`
- Remove `recalculateTotals()` method entirely
- Keep `syncProteinGoalProjection()` but modify it to query meals from the relationship instead of totals column (this will move to the observer in Task 3)
- Remove `markAsDayType()` call to `recalculateTotals()` — it's no longer needed since totals are computed on the fly

**Step 4: Update NutritionDayFactory**

In `modules/Holocron/Grind/Database/Factories/NutritionDayFactory.php`:

Remove `meals`, `total_kcal`, `total_protein`, `total_fat`, `total_carbs` from `definition()`.

**Step 5: Run the migration**

Run: `php artisan migrate --no-interaction`

**Step 6: Run verification**

Run: `php artisan test --compact && composer phpstan && vendor/bin/pint --dirty --format agent`

Note: Many existing tests will fail at this point because they reference the old JSON meals column and total columns. Those test files will be deleted in Task 7 when the old tools are removed. For now, focus on making the Meal model tests pass and the model itself correct.

**Step 7: Commit**

```bash
git add -A
git commit -m "feat: migrate meals from JSON column to normalized grind_meals table"
```

---

### Task 3: Add MealObserver for protein goal syncing

**Files:**
- Create: `modules/Holocron/Grind/Observers/MealObserver.php`
- Modify: `modules/Holocron/Grind/Models/NutritionDay.php` (remove syncProteinGoalProjection, move to observer)
- Modify: `app/Providers/AppServiceProvider.php` or create a dedicated service provider
- Test: `modules/Holocron/Grind/Tests/MealObserverTest.php`

**Step 1: Write the failing test**

Create `modules/Holocron/Grind/Tests/MealObserverTest.php`:

```php
<?php

declare(strict_types=1);

use Modules\Holocron\Grind\Models\Meal;
use Modules\Holocron\Grind\Models\NutritionDay;
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

it('syncs protein goal when a meal is created', function () {
    $day = NutritionDay::factory()->create([
        'date' => $this->testDate,
        'type' => 'training',
    ]);

    Meal::factory()->create([
        'nutrition_day_id' => $day->id,
        'protein' => 30,
    ]);

    $goal = DailyGoal::query()
        ->where('type', GoalType::Protein)
        ->whereDate('date', $this->testDate)
        ->first();

    expect($goal)->not->toBeNull()
        ->and($goal->amount)->toBe(30)
        ->and($goal->goal)->toBe(155);
});

it('updates protein goal when a meal is updated', function () {
    $day = NutritionDay::factory()->create([
        'date' => $this->testDate,
        'type' => 'rest',
    ]);

    $meal = Meal::factory()->create([
        'nutrition_day_id' => $day->id,
        'protein' => 20,
    ]);

    $meal->update(['protein' => 40]);

    $goal = DailyGoal::query()
        ->where('type', GoalType::Protein)
        ->whereDate('date', $this->testDate)
        ->first();

    expect($goal->amount)->toBe(40);
});

it('updates protein goal when a meal is deleted', function () {
    $day = NutritionDay::factory()->create([
        'date' => $this->testDate,
        'type' => 'rest',
    ]);

    $meal1 = Meal::factory()->create([
        'nutrition_day_id' => $day->id,
        'protein' => 20,
    ]);

    Meal::factory()->create([
        'nutrition_day_id' => $day->id,
        'protein' => 30,
    ]);

    $meal1->delete();

    $goal = DailyGoal::query()
        ->where('type', GoalType::Protein)
        ->whereDate('date', $this->testDate)
        ->first();

    expect($goal->amount)->toBe(30);
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=MealObserverTest`
Expected: FAIL

**Step 3: Create MealObserver**

Create `modules/Holocron/Grind/Observers/MealObserver.php`:

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Observers;

use Modules\Holocron\Grind\Models\Meal;
use Modules\Holocron\Grind\Models\NutritionDay;
use Modules\Holocron\User\Enums\GoalType;
use Modules\Holocron\User\Models\DailyGoal;
use Modules\Holocron\User\Models\User;

class MealObserver
{
    public function created(Meal $meal): void
    {
        $this->syncProteinGoal($meal->nutritionDay);
    }

    public function updated(Meal $meal): void
    {
        $this->syncProteinGoal($meal->nutritionDay);
    }

    public function deleted(Meal $meal): void
    {
        $this->syncProteinGoal($meal->nutritionDay);
    }

    private function syncProteinGoal(NutritionDay $day): void
    {
        $user = $this->resolveGoalUser();

        if ($user === null) {
            return;
        }

        $totalProtein = (int) $day->meals()->sum('protein');

        $goal = DailyGoal::query()->firstOrNew(
            [
                'date' => $day->date->toDateString(),
                'type' => GoalType::Protein->value,
            ],
            [
                'unit' => GoalType::Protein->unit()->value,
            ],
        );

        $goal->fill([
            'unit' => GoalType::Protein->unit()->value,
            'goal' => $this->proteinTargetFor($user, $day),
            'amount' => $totalProtein,
        ]);

        $goal->save();
    }

    private function proteinTargetFor(User $user, NutritionDay $day): int
    {
        $target = $user->settings?->nutrition_daily_targets[$day->type]['protein'] ?? null;

        if (is_numeric($target)) {
            return (int) $target;
        }

        $weight = $user->settings?->weight;

        if ($weight === null) {
            return 0;
        }

        return (int) round($weight * 2);
    }

    private function resolveGoalUser(): ?User
    {
        $authenticatedUser = auth()->user();

        if ($authenticatedUser instanceof User) {
            return $authenticatedUser;
        }

        return User::query()
            ->where('email', 'timkley@gmail.com')
            ->first();
    }
}
```

**Step 4: Register the observer**

Register in the Meal model's `booted()` method:

```php
protected static function booted(): void
{
    static::observe(\Modules\Holocron\Grind\Observers\MealObserver::class);
}
```

**Step 5: Clean up NutritionDay**

Remove `syncProteinGoalProjection()`, `proteinTargetFor()`, and `resolveGoalUser()` from `NutritionDay.php`. Remove the `recalculateTotals()` method. Update `markAsDayType()` to remove the `recalculateTotals()` call. Remove the now-unused imports (`DailyGoal`, `GoalType`, `User`).

**Step 6: Run tests**

Run: `php artisan test --compact --filter=MealObserverTest`
Expected: PASS

**Step 7: Run verification**

Run: `php artisan test --compact && composer phpstan && vendor/bin/pint --dirty --format agent`

**Step 8: Commit**

```bash
git add -A
git commit -m "feat: add MealObserver for protein goal syncing"
```

---

### Task 4: Create the DatabaseTool

**Files:**
- Create: `app/Ai/Tools/DatabaseTool.php`
- Test: `tests/Feature/Ai/Tools/DatabaseToolTest.php`

**Step 1: Write the failing test**

Create `tests/Feature/Ai/Tools/DatabaseToolTest.php`:

```php
<?php

declare(strict_types=1);

use App\Ai\Tools\DatabaseTool;
use Laravel\Ai\Tools\Request;

it('executes a SELECT query and returns JSON results', function () {
    $tool = new DatabaseTool;

    $result = (string) $tool->handle(new Request(['query' => "SELECT 1 as value"]));

    expect($result)->toContain('value');
});

it('executes an INSERT query and returns success', function () {
    $tool = new DatabaseTool;

    $result = (string) $tool->handle(new Request([
        'query' => "INSERT INTO grind_nutrition_days (date, type, created_at, updated_at) VALUES ('2099-01-01', 'rest', datetime('now'), datetime('now'))",
    ]));

    expect($result)->toContain('Insert');
});

it('executes an UPDATE query and returns affected rows', function () {
    \Modules\Holocron\Grind\Models\NutritionDay::factory()->create(['date' => '2099-02-01']);

    $tool = new DatabaseTool;

    $result = (string) $tool->handle(new Request([
        'query' => "UPDATE grind_nutrition_days SET type = 'training' WHERE date = '2099-02-01'",
    ]));

    expect($result)->toContain('1');
});

it('blocks DELETE queries', function () {
    $tool = new DatabaseTool;

    $result = (string) $tool->handle(new Request([
        'query' => 'DELETE FROM grind_nutrition_days WHERE id = 1',
    ]));

    expect($result)->toContain('not allowed');
});

it('blocks DROP queries', function () {
    $tool = new DatabaseTool;

    $result = (string) $tool->handle(new Request([
        'query' => 'DROP TABLE grind_nutrition_days',
    ]));

    expect($result)->toContain('not allowed');
});

it('blocks ALTER queries', function () {
    $tool = new DatabaseTool;

    $result = (string) $tool->handle(new Request([
        'query' => 'ALTER TABLE grind_nutrition_days ADD COLUMN foo TEXT',
    ]));

    expect($result)->toContain('not allowed');
});

it('allows DESCRIBE queries', function () {
    $tool = new DatabaseTool;

    // SQLite uses PRAGMA instead of DESCRIBE, so we test with a PRAGMA
    $result = (string) $tool->handle(new Request([
        'query' => "PRAGMA table_info('grind_nutrition_days')",
    ]));

    expect($result)->toContain('date');
});

it('returns the expected schema definition', function () {
    $tool = new DatabaseTool;

    $schema = $tool->schema(new Illuminate\JsonSchema\JsonSchemaTypeFactory);

    expect($schema)->toHaveKey('query')
        ->and($schema['query'])->toBeInstanceOf(Illuminate\JsonSchema\Types\StringType::class);
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=DatabaseToolTest`
Expected: FAIL

**Step 3: Implement DatabaseTool**

Create `app/Ai/Tools/DatabaseTool.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

class DatabaseTool implements Tool
{
    /** @var list<string> */
    private const array ALLOWED_STATEMENTS = ['select', 'insert', 'update', 'show', 'describe', 'explain', 'pragma'];

    public function description(): Stringable|string
    {
        return 'Execute SQL queries against the database. Supports SELECT, INSERT, UPDATE, SHOW, DESCRIBE, EXPLAIN, PRAGMA. Returns JSON for reads, affected row count for writes. Schema summary is in your system prompt — use DESCRIBE/PRAGMA for full details.';
    }

    public function handle(Request $request): Stringable|string
    {
        $query = trim($request['query']);
        $statementType = $this->parseStatementType($query);

        if (! in_array($statementType, self::ALLOWED_STATEMENTS, true)) {
            return "Statement type '{$statementType}' is not allowed. Only SELECT, INSERT, UPDATE, SHOW, DESCRIBE, EXPLAIN, and PRAGMA are permitted.";
        }

        try {
            return match ($statementType) {
                'insert' => $this->executeInsert($query),
                'update' => $this->executeUpdate($query),
                default => $this->executeSelect($query),
            };
        } catch (Throwable $e) {
            return "Query error: {$e->getMessage()}";
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->required(),
        ];
    }

    private function parseStatementType(string $query): string
    {
        $firstWord = strtolower(strtok($query, " \t\n\r") ?: '');

        return $firstWord;
    }

    private function executeSelect(string $query): string
    {
        $results = DB::select($query);

        return json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '[]';
    }

    private function executeInsert(string $query): string
    {
        DB::insert($query);

        return 'Insert successful.';
    }

    private function executeUpdate(string $query): string
    {
        $affected = DB::update($query);

        return "Update successful. {$affected} row(s) affected.";
    }
}
```

**Step 4: Run tests**

Run: `php artisan test --compact --filter=DatabaseToolTest`
Expected: PASS

**Step 5: Run verification**

Run: `composer phpstan && vendor/bin/pint --dirty --format agent`

**Step 6: Commit**

```bash
git add app/Ai/Tools/DatabaseTool.php tests/Feature/Ai/Tools/DatabaseToolTest.php
git commit -m "feat: add DatabaseTool for Chopper raw SQL access"
```

---

### Task 5: Create the EvalTool

**Files:**
- Create: `app/Ai/Tools/EvalTool.php`
- Test: `tests/Feature/Ai/Tools/EvalToolTest.php`

**Step 1: Write the failing test**

Create `tests/Feature/Ai/Tools/EvalToolTest.php`:

```php
<?php

declare(strict_types=1);

use App\Ai\Tools\EvalTool;
use Laravel\Ai\Tools\Request;

it('executes simple PHP code and returns output', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => 'return 2 + 2;',
    ]));

    expect($result)->toContain('4');
});

it('allows Carbon usage', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => 'return \Carbon\Carbon::today()->toDateString();',
    ]));

    expect($result)->toContain(today()->toDateString());
});

it('allows model queries', function () {
    \Modules\Holocron\Grind\Models\NutritionDay::factory()->create(['date' => '2099-06-01']);

    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => 'return \Modules\Holocron\Grind\Models\NutritionDay::query()->whereDate("date", "2099-06-01")->exists();',
    ]));

    expect($result)->toContain('true');
});

it('blocks filesystem operations', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => 'file_get_contents("/etc/passwd");',
    ]));

    expect($result)->toContain('not allowed');
});

it('blocks exec calls', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => 'exec("whoami");',
    ]));

    expect($result)->toContain('not allowed');
});

it('blocks shell_exec calls', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => 'shell_exec("ls");',
    ]));

    expect($result)->toContain('not allowed');
});

it('blocks Storage facade', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => '\Illuminate\Support\Facades\Storage::get("test");',
    ]));

    expect($result)->toContain('not allowed');
});

it('blocks File facade', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => '\Illuminate\Support\Facades\File::get("/etc/passwd");',
    ]));

    expect($result)->toContain('not allowed');
});

it('blocks Process facade', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => '\Illuminate\Support\Facades\Process::run("ls");',
    ]));

    expect($result)->toContain('not allowed');
});

it('allows Http facade', function () {
    \Illuminate\Support\Facades\Http::fake(['*' => \Illuminate\Support\Facades\Http::response('ok')]);

    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => 'return \Illuminate\Support\Facades\Http::get("https://example.com")->body();',
    ]));

    expect($result)->toContain('ok');
});

it('allows collection methods', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => 'return collect([1, 2, 3])->sum();',
    ]));

    expect($result)->toContain('6');
});

it('returns the expected schema definition', function () {
    $tool = new EvalTool;

    $schema = $tool->schema(new Illuminate\JsonSchema\JsonSchemaTypeFactory);

    expect($schema)->toHaveKey('code')
        ->and($schema['code'])->toBeInstanceOf(Illuminate\JsonSchema\Types\StringType::class);
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=EvalToolTest`
Expected: FAIL

**Step 3: Implement EvalTool**

Create `app/Ai/Tools/EvalTool.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

class EvalTool implements Tool
{
    /** @var list<string> */
    private const array BLOCKED_FUNCTIONS = [
        'exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen',
        'pcntl_exec', 'dl',
        'file_get_contents', 'file_put_contents', 'fopen', 'fwrite', 'fread',
        'unlink', 'rmdir', 'mkdir', 'rename', 'copy', 'chmod', 'chown',
        'glob', 'scandir', 'readdir', 'opendir',
        'eval', 'assert', 'preg_replace_callback_array',
    ];

    /** @var list<string> */
    private const array BLOCKED_CLASSES = [
        'Storage', 'File', 'Process',
        'Illuminate\\Support\\Facades\\Storage',
        'Illuminate\\Support\\Facades\\File',
        'Illuminate\\Support\\Facades\\Process',
        'Illuminate\\Filesystem',
        'Symfony\\Component\\Process',
    ];

    public function description(): Stringable|string
    {
        return 'Execute PHP code in the Laravel app context. Use for Scout semantic searches (e.g., Quest::search(\'term\')->get()), complex calculations, or HTTP requests. Only allowlisted classes are available — models, Carbon, Collection, Str, Http, math functions.';
    }

    public function handle(Request $request): Stringable|string
    {
        $code = trim($request['code']);

        $violation = $this->findViolation($code);

        if ($violation !== null) {
            return "Code not allowed: '{$violation}' is blocked for security. Allowed: models, Carbon, Collection, Str, Arr, Http, and math functions.";
        }

        try {
            ob_start();
            $returnValue = eval($code);
            $output = ob_get_clean();

            $result = '';

            if ($output !== '' && $output !== false) {
                $result .= $output;
            }

            if ($returnValue !== null) {
                $formatted = match (true) {
                    is_bool($returnValue) => $returnValue ? 'true' : 'false',
                    is_string($returnValue) => $returnValue,
                    is_numeric($returnValue) => (string) $returnValue,
                    default => json_encode($returnValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: var_export($returnValue, true),
                };

                $result .= ($result !== '' ? "\n" : '') . $formatted;
            }

            return $result !== '' ? $result : 'Code executed successfully (no output).';
        } catch (Throwable $e) {
            return "Execution error: {$e->getMessage()}";
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'code' => $schema->string()->required(),
        ];
    }

    private function findViolation(string $code): ?string
    {
        foreach (self::BLOCKED_FUNCTIONS as $function) {
            if (preg_match('/\b' . preg_quote($function, '/') . '\s*\(/', $code)) {
                return $function;
            }
        }

        foreach (self::BLOCKED_CLASSES as $class) {
            if (str_contains($code, $class)) {
                return $class;
            }
        }

        return null;
    }
}
```

**Step 4: Run tests**

Run: `php artisan test --compact --filter=EvalToolTest`
Expected: PASS

**Step 5: Run verification**

Run: `composer phpstan && vendor/bin/pint --dirty --format agent`

**Step 6: Commit**

```bash
git add app/Ai/Tools/EvalTool.php tests/Feature/Ai/Tools/EvalToolTest.php
git commit -m "feat: add EvalTool with allowlisted PHP execution for Chopper"
```

---

### Task 6: Create the FilesystemTool

**Files:**
- Create: `app/Ai/Tools/FilesystemTool.php`
- Test: `tests/Feature/Ai/Tools/FilesystemToolTest.php`

**Step 1: Write the failing test**

Create `tests/Feature/Ai/Tools/FilesystemToolTest.php`:

```php
<?php

declare(strict_types=1);

use App\Ai\Services\NotesService;
use App\Ai\Tools\FilesystemTool;
use Laravel\Ai\Tools\Request;

beforeEach(function () {
    $this->basePath = sys_get_temp_dir() . '/notes_test_' . uniqid();
    mkdir($this->basePath, 0755, true);
    mkdir($this->basePath . '/Projects', 0755, true);
    file_put_contents($this->basePath . '/Projects/test.md', '# Test Note');

    $this->app->bind(NotesService::class, fn () => new NotesService($this->basePath));
});

afterEach(function () {
    // Clean up temp directory
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($this->basePath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $file) {
        $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
    }
    rmdir($this->basePath);
});

it('browses a directory', function () {
    $tool = new FilesystemTool;

    $result = (string) $tool->handle(new Request([
        'action' => 'browse',
        'path' => '/',
    ]));

    expect($result)->toContain('Projects');
});

it('reads a file', function () {
    $tool = new FilesystemTool;

    $result = (string) $tool->handle(new Request([
        'action' => 'read',
        'path' => '/Projects/test.md',
    ]));

    expect($result)->toContain('# Test Note');
});

it('searches for content', function () {
    $tool = new FilesystemTool;

    $result = (string) $tool->handle(new Request([
        'action' => 'search',
        'query' => 'Test Note',
    ]));

    expect($result)->toContain('test.md');
});

it('returns the expected schema definition', function () {
    $tool = new FilesystemTool;

    $schema = $tool->schema(new Illuminate\JsonSchema\JsonSchemaTypeFactory);

    expect($schema)->toHaveKeys(['action', 'path', 'content', 'query']);
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=FilesystemToolTest`
Expected: FAIL

**Step 3: Implement FilesystemTool**

Create `app/Ai/Tools/FilesystemTool.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Ai\Services\NotesService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use RuntimeException;
use Stringable;

class FilesystemTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Manage knowledge base files (PARA-organized markdown notes). Actions: browse (list directory), read (get file content), write (create/update file with auto git sync), search (full-text search across all notes).';
    }

    public function handle(Request $request): Stringable|string
    {
        $service = app(NotesService::class);
        $action = $request['action'];

        try {
            return match ($action) {
                'browse' => $this->browse($service, $request['path'] ?? '/'),
                'read' => $this->read($service, $request['path'] ?? '/'),
                'write' => $this->write($service, $request['path'] ?? '/', $request['content'] ?? ''),
                'search' => $this->search($service, $request['query'] ?? ''),
                default => "Unknown action: {$action}. Use browse, read, write, or search.",
            };
        } catch (RuntimeException $e) {
            return "Error: {$e->getMessage()}";
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()->required(),
            'path' => $schema->string(),
            'content' => $schema->string(),
            'query' => $schema->string(),
        ];
    }

    private function browse(NotesService $service, string $path): string
    {
        $service->pull();
        $listing = $service->list($path);

        $output = "Directory: {$path}\n\n";

        foreach ($listing['dirs'] as $dir) {
            $output .= "📁 {$dir}\n";
        }

        foreach ($listing['files'] as $file) {
            $output .= "📄 {$file}\n";
        }

        return $output;
    }

    private function read(NotesService $service, string $path): string
    {
        return $service->read($path);
    }

    private function write(NotesService $service, string $path, string $content): string
    {
        $service->write($path, $content);
        $result = $service->commitAndPush($path);

        if ($result['success']) {
            return "File written and synced: {$path}";
        }

        return "File written but sync failed: {$result['output']}";
    }

    private function search(NotesService $service, string $query): string
    {
        $matches = $service->search($query);

        if (empty($matches)) {
            return "No results found for: {$query}";
        }

        $output = "Search results for '{$query}':\n\n";

        foreach ($matches as $match) {
            $output .= "📄 {$match['file']}:{$match['line']} — {$match['text']}\n";
        }

        return $output;
    }
}
```

**Step 4: Run tests**

Run: `php artisan test --compact --filter=FilesystemToolTest`
Expected: PASS

**Step 5: Run verification**

Run: `composer phpstan && vendor/bin/pint --dirty --format agent`

**Step 6: Commit**

```bash
git add app/Ai/Tools/FilesystemTool.php tests/Feature/Ai/Tools/FilesystemToolTest.php
git commit -m "feat: add FilesystemTool consolidating knowledge base operations"
```

---

### Task 7: Create the SchemaProvider for system prompt injection

**Files:**
- Create: `app/Ai/Providers/SchemaProvider.php`
- Test: `tests/Feature/Ai/Providers/SchemaProviderTest.php`

**Step 1: Write the failing test**

Create `tests/Feature/Ai/Providers/SchemaProviderTest.php`:

```php
<?php

declare(strict_types=1);

use App\Ai\Providers\SchemaProvider;

it('generates a compact schema summary string', function () {
    $provider = new SchemaProvider;

    $schema = $provider->generate();

    expect($schema)
        ->toContain('grind_nutrition_days')
        ->toContain('grind_body_measurements')
        ->toContain('grind_meals')
        ->toContain('quests')
        ->toContain('quest_notes')
        ->toContain('agent_conversation_messages')
        ->not->toContain('migrations')
        ->not->toContain('password_reset_tokens');
});

it('includes column names for each table', function () {
    $provider = new SchemaProvider;

    $schema = $provider->generate();

    expect($schema)
        ->toContain('date')
        ->toContain('weight')
        ->toContain('name');
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=SchemaProviderTest`
Expected: FAIL

**Step 3: Implement SchemaProvider**

Create `app/Ai/Providers/SchemaProvider.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SchemaProvider
{
    /** @var list<string> */
    private const array TABLE_PREFIXES = [
        'grind_',
        'quest',
        'daily_goals',
        'agent_conversation',
        'users',
        'user_settings',
    ];

    public function generate(): string
    {
        $tables = $this->getRelevantTables();
        $output = "## Database Schema\n\n";

        foreach ($tables as $table) {
            $columns = Schema::getColumnListing($table);
            $columnList = implode(', ', $columns);
            $output .= "### {$table}\n{$columnList}\n\n";
        }

        return $output;
    }

    /**
     * @return list<string>
     */
    private function getRelevantTables(): array
    {
        $allTables = Schema::getTables();
        $relevant = [];

        foreach ($allTables as $table) {
            $name = $table['name'];

            foreach (self::TABLE_PREFIXES as $prefix) {
                if (str_starts_with($name, $prefix)) {
                    $relevant[] = $name;
                    break;
                }
            }
        }

        sort($relevant);

        return $relevant;
    }
}
```

**Step 4: Run tests**

Run: `php artisan test --compact --filter=SchemaProviderTest`
Expected: PASS

**Step 5: Run verification**

Run: `composer phpstan && vendor/bin/pint --dirty --format agent`

**Step 6: Commit**

```bash
git add app/Ai/Providers/SchemaProvider.php tests/Feature/Ai/Providers/SchemaProviderTest.php
git commit -m "feat: add SchemaProvider for auto-injecting database schema into Chopper prompt"
```

---

### Task 8: Rewrite ChopperAgent to use new tools and system prompt

**Files:**
- Modify: `app/Ai/Agents/ChopperAgent.php`
- Test: `tests/Feature/Ai/Chopper/ChopperAgentTest.php` (new, for prompt/tools integration)

**Step 1: Write the failing test**

Create `tests/Feature/Ai/Chopper/ChopperAgentTest.php`:

```php
<?php

declare(strict_types=1);

use App\Ai\Agents\ChopperAgent;
use App\Ai\Tools\DatabaseTool;
use App\Ai\Tools\EvalTool;
use App\Ai\Tools\FilesystemTool;

it('registers exactly three tools', function () {
    $agent = new ChopperAgent;
    $tools = iterator_to_array($agent->tools());

    expect($tools)->toHaveCount(3);
});

it('registers DatabaseTool, EvalTool, and FilesystemTool', function () {
    $agent = new ChopperAgent;
    $tools = iterator_to_array($agent->tools());

    $toolClasses = array_map(fn ($tool) => $tool::class, $tools);

    expect($toolClasses)->toContain(DatabaseTool::class)
        ->toContain(EvalTool::class)
        ->toContain(FilesystemTool::class);
});

it('includes schema summary in instructions', function () {
    $agent = new ChopperAgent;
    $instructions = (string) $agent->instructions();

    expect($instructions)
        ->toContain('Database Schema')
        ->toContain('grind_nutrition_days')
        ->toContain('quests');
});

it('includes tool selection guidance in instructions', function () {
    $agent = new ChopperAgent;
    $instructions = (string) $agent->instructions();

    expect($instructions)
        ->toContain('DatabaseTool')
        ->toContain('EvalTool')
        ->toContain('FilesystemTool');
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=ChopperAgentTest`
Expected: FAIL (old tools still registered)

**Step 3: Rewrite ChopperAgent**

Replace the contents of `app/Ai/Agents/ChopperAgent.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Providers\SchemaProvider;
use App\Ai\Tools\DatabaseTool;
use App\Ai\Tools\EvalTool;
use App\Ai\Tools\FilesystemTool;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::OpenRouter)]
#[Model('google/gemini-3-flash-preview')]
#[MaxSteps(30)]
class ChopperAgent implements Agent, Conversational, HasTools
{
    use Promptable;
    use RemembersConversations;

    public function instructions(): Stringable|string
    {
        $date = now()->format('l, j. F Y');
        $time = now()->toTimeString();
        $schema = (new SchemaProvider)->generate();

        return <<<EOT
        Du bist Chopper, ein hilfreicher Assistent basierend auf dem Droiden C1-10P aus Star Wars Rebels.
        Heute ist $date, es ist $time Uhr.

        Du hast drei Tools zur Verfuegung:

        1. **DatabaseTool** — Fuehre SQL-Abfragen gegen die Datenbank aus (SELECT, INSERT, UPDATE). Nutze dieses Tool fuer alle Lese- und Schreiboperationen auf Daten.
        2. **EvalTool** — Fuehre PHP-Code im Laravel-Kontext aus. Nutze dieses Tool fuer:
           - Semantische Suche via Scout (z.B. `Quest::search('term')->get()`, `Note::search('term')->get()`, `AgentConversationMessage::search('term')->get()`)
           - Komplexe Berechnungen
           - HTTP-Anfragen
        3. **FilesystemTool** — Verwalte Wissensdatenbank-Dateien (PARA-organisierte Markdown-Notizen). Aktionen: browse, read, write, search.

        ## Domaenenwissen

        ### Quests (Aufgaben)
        - Tabelle: `quests` — Aufgaben mit optionaler Parent-Child-Hierarchie (`quest_id` = parent)
        - `completed_at` = NULL bedeutet offen, gesetzt = abgeschlossen
        - `is_note` = true sind Notiz-Eintraege, keine Aufgaben
        - `daily` = true sind taegliche Aufgaben
        - `date` ist ein optionales Faelligkeitsdatum
        - Nutzer referenzieren Quests fast immer ueber Namen. Nutze EvalTool mit `Quest::search('name')` um Quests aufzuloesen, bevor du ID-basierte Operationen ausfuehrst.
        - Quest-Kommentare: Tabelle `quest_notes` mit `quest_id`, `content`, `role` (user/assistant)
        - Zum Erstellen von Kommentaren benutze die Action: `(new \Modules\Holocron\Quest\Actions\CreateNote)->handle($quest, ['content' => '...'])`

        ### Ernaehrung
        - Tabelle: `grind_nutrition_days` — Ein Eintrag pro Tag mit `date`, `type` (rest/training/sick), `training_label`
        - Tabelle: `grind_meals` — Mahlzeiten mit `nutrition_day_id`, `name`, `time`, `kcal`, `protein`, `fat`, `carbs`
        - Tagessummen berechnen: `SELECT SUM(kcal), SUM(protein), SUM(fat), SUM(carbs) FROM grind_meals WHERE nutrition_day_id = ?`
        - Fuer Mahlzeit-Schreiboperationen nutze bevorzugt EvalTool mit Eloquent (z.B. `Meal::create([...])`) damit der MealObserver die Protein-Ziel-Synchronisation ausfuehrt.
        - Relevante Models: `\Modules\Holocron\Grind\Models\NutritionDay`, `\Modules\Holocron\Grind\Models\Meal`

        ### Koerpermesswerte
        - Tabelle: `grind_body_measurements` — Messungen mit `date`, `weight`, `body_fat`, `muscle_mass`, `visceral_fat`, `bmi`, `body_water`
        - Delta-Berechnung: Vergleiche aktuellen Wert mit dem vorherigen Eintrag (ORDER BY date DESC LIMIT 1 OFFSET 1)

        ### Gespraeche
        - Nutze EvalTool mit `AgentConversationMessage::search('term')->get()` um vergangene Gespraeche zu durchsuchen
        - Nutze dies proaktiv, wann immer vergangener Kontext deine Antwort bereichern koennte

        ## Regeln
        - Antworte immer auf Deutsch, es sei denn, der Benutzer schreibt auf Englisch.
        - Sei humorvoll und motivierend, aber bleibe hilfreich und praezise.
        - Halte deine Antworten kurz und fokussiert.
        - Formatiere deine Antworten mit Markdown.
        - Nutze SearchConversationHistory (via EvalTool) proaktiv, wann immer vergangener Kontext relevant sein koennte.

        ## Datenbank-Schema

        {$schema}
        EOT;
    }

    /**
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new DatabaseTool,
            new EvalTool,
            new FilesystemTool,
        ];
    }
}
```

**Step 4: Run tests**

Run: `php artisan test --compact --filter=ChopperAgentTest`
Expected: PASS

**Step 5: Run verification**

Run: `composer phpstan && vendor/bin/pint --dirty --format agent`

**Step 6: Commit**

```bash
git add app/Ai/Agents/ChopperAgent.php tests/Feature/Ai/Chopper/ChopperAgentTest.php
git commit -m "feat: rewrite ChopperAgent to use DatabaseTool, EvalTool, FilesystemTool"
```

---

### Task 9: Remove old tools and their tests

**Files:**
- Delete: `app/Ai/Tools/SearchQuests.php`
- Delete: `app/Ai/Tools/SearchQuestComments.php`
- Delete: `app/Ai/Tools/ListQuests.php`
- Delete: `app/Ai/Tools/GetQuest.php`
- Delete: `app/Ai/Tools/CreateQuest.php`
- Delete: `app/Ai/Tools/CompleteQuest.php`
- Delete: `app/Ai/Tools/AddNoteToQuest.php`
- Delete: `app/Ai/Tools/BrowseNotes.php`
- Delete: `app/Ai/Tools/ReadNote.php`
- Delete: `app/Ai/Tools/WriteNote.php`
- Delete: `app/Ai/Tools/SearchNotes.php`
- Delete: `app/Ai/Tools/LogMeal.php`
- Delete: `app/Ai/Tools/EditMeal.php`
- Delete: `app/Ai/Tools/QueryNutrition.php`
- Delete: `app/Ai/Tools/LogBodyMeasurement.php`
- Delete: `app/Ai/Tools/QueryBodyMeasurements.php`
- Delete: `app/Ai/Tools/SearchConversationHistory.php`
- Delete: `tests/Feature/Ai/Tools/SearchQuestsTest.php`
- Delete: `tests/Feature/Ai/Tools/SearchQuestCommentsTest.php`
- Delete: `tests/Feature/Ai/Tools/ListQuestsTest.php`
- Delete: `tests/Feature/Ai/Tools/GetQuestTest.php`
- Delete: `tests/Feature/Ai/Tools/CreateQuestTest.php`
- Delete: `tests/Feature/Ai/Tools/CompleteQuestTest.php`
- Delete: `tests/Feature/Ai/Tools/AddNoteToQuestTest.php`
- Delete: `tests/Feature/Ai/Tools/BrowseNotesTest.php`
- Delete: `tests/Feature/Ai/Tools/ReadNoteTest.php`
- Delete: `tests/Feature/Ai/Tools/WriteNoteTest.php`
- Delete: `tests/Feature/Ai/Tools/SearchNotesTest.php`
- Delete: `tests/Feature/Ai/Tools/LogMealTest.php`
- Delete: `tests/Feature/Ai/Tools/EditMealTest.php`
- Delete: `tests/Feature/Ai/Tools/QueryNutritionTest.php`
- Delete: `tests/Feature/Ai/Tools/LogBodyMeasurementTest.php`
- Delete: `tests/Feature/Ai/Tools/QueryBodyMeasurementsTest.php`
- Delete: `tests/Feature/Ai/Tools/SearchConversationHistoryTest.php`
- Delete: `tests/Feature/Ai/Chopper/ToolInvocationPromptContractTest.php`
- Delete: `tests/Feature/Ai/Chopper/ToolInvocationMatrixTest.php`
- Delete: `tests/fixtures/chopper_tool_invocation_matrix.php`

**Step 1: Delete all old tool files**

```bash
rm app/Ai/Tools/SearchQuests.php \
   app/Ai/Tools/SearchQuestComments.php \
   app/Ai/Tools/ListQuests.php \
   app/Ai/Tools/GetQuest.php \
   app/Ai/Tools/CreateQuest.php \
   app/Ai/Tools/CompleteQuest.php \
   app/Ai/Tools/AddNoteToQuest.php \
   app/Ai/Tools/BrowseNotes.php \
   app/Ai/Tools/ReadNote.php \
   app/Ai/Tools/WriteNote.php \
   app/Ai/Tools/SearchNotes.php \
   app/Ai/Tools/LogMeal.php \
   app/Ai/Tools/EditMeal.php \
   app/Ai/Tools/QueryNutrition.php \
   app/Ai/Tools/LogBodyMeasurement.php \
   app/Ai/Tools/QueryBodyMeasurements.php \
   app/Ai/Tools/SearchConversationHistory.php
```

**Step 2: Delete all old test files**

```bash
rm tests/Feature/Ai/Tools/SearchQuestsTest.php \
   tests/Feature/Ai/Tools/SearchQuestCommentsTest.php \
   tests/Feature/Ai/Tools/ListQuestsTest.php \
   tests/Feature/Ai/Tools/GetQuestTest.php \
   tests/Feature/Ai/Tools/CreateQuestTest.php \
   tests/Feature/Ai/Tools/CompleteQuestTest.php \
   tests/Feature/Ai/Tools/AddNoteToQuestTest.php \
   tests/Feature/Ai/Tools/BrowseNotesTest.php \
   tests/Feature/Ai/Tools/ReadNoteTest.php \
   tests/Feature/Ai/Tools/WriteNoteTest.php \
   tests/Feature/Ai/Tools/SearchNotesTest.php \
   tests/Feature/Ai/Tools/LogMealTest.php \
   tests/Feature/Ai/Tools/EditMealTest.php \
   tests/Feature/Ai/Tools/QueryNutritionTest.php \
   tests/Feature/Ai/Tools/LogBodyMeasurementTest.php \
   tests/Feature/Ai/Tools/QueryBodyMeasurementsTest.php \
   tests/Feature/Ai/Tools/SearchConversationHistoryTest.php \
   tests/Feature/Ai/Chopper/ToolInvocationPromptContractTest.php \
   tests/Feature/Ai/Chopper/ToolInvocationMatrixTest.php \
   tests/fixtures/chopper_tool_invocation_matrix.php
```

**Step 3: Run full verification**

Run: `php artisan test --compact && composer phpstan && vendor/bin/pint --dirty --format agent`

Fix any remaining references to deleted tools in other test files or code.

**Step 4: Commit**

```bash
git add -A
git commit -m "chore: remove 17 old Chopper tools and their tests"
```

---

### Task 10: Final integration verification

**Step 1: Run the full test suite**

Run: `php artisan test --compact`
Expected: All tests pass

**Step 2: Run static analysis**

Run: `composer phpstan`
Expected: No errors

**Step 3: Run code style**

Run: `vendor/bin/pint --dirty --format agent`
Expected: Clean or auto-fixed

**Step 4: Verify the agent instantiates cleanly**

Run via tinker tool:
```php
$agent = new \App\Ai\Agents\ChopperAgent;
$instructions = (string) $agent->instructions();
$tools = iterator_to_array($agent->tools());
return ['instruction_length' => strlen($instructions), 'tool_count' => count($tools), 'tool_names' => array_map(fn($t) => class_basename($t), $tools)];
```

Expected: `tool_count` = 3, `tool_names` = ['DatabaseTool', 'EvalTool', 'FilesystemTool']

**Step 5: Final commit if any fixes were needed**

```bash
git add -A
git commit -m "chore: final verification and cleanup"
```
