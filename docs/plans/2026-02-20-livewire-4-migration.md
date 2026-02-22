# Livewire 3 → 4 Migration Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Migrate the application from Livewire 3.7.6 to Livewire 4.x, fixing all breaking changes and converting routes.

**Architecture:** Big bang migration — update the dependency, fix all breaking changes (stream API, JS hooks, wire:model modifiers, layout directive), convert all Livewire routes to `Route::livewire()`, then verify with the full test suite.

**Tech Stack:** Livewire 4, Flux UI 2.x, Laravel 12, PHP 8.4, Pest 4

---

### Task 1: Update Composer Dependencies

**Files:**
- Modify: `composer.json:23` (`livewire/livewire` version constraint)

**Step 1: Update livewire/livewire to v4 and update Flux UI**

Run:
```bash
composer require livewire/livewire:^4.0
composer update livewire/flux livewire/flux-pro
```

Expected: Composer resolves and installs Livewire 4.x with compatible Flux UI versions.

**Step 2: Clear caches**

Run:
```bash
php artisan optimize:clear
```

Expected: "Compiled views cleared" and similar cache-clearing messages.

**Step 3: Rebuild frontend assets**

Run:
```bash
npm run build
```

Expected: Vite build completes successfully.

**Step 4: Commit**

```bash
git add composer.json composer.lock
git commit -m "chore: upgrade livewire/livewire to v4"
```

---

### Task 2: Fix `$this->stream()` API Signature

The v4 signature changes: content becomes the first positional argument, `to:` is renamed to `el:`.

**Files:**
- Modify: `modules/Holocron/_Shared/Livewire/Chopper.php:69-73`
- Modify: `modules/Holocron/Quest/Livewire/WithNotes.php:104-108`

**Step 1: Update Chopper.php**

In `modules/Holocron/_Shared/Livewire/Chopper.php`, change line 69-73 from:
```php
$this->stream(
    to: 'answer',
    content: str($this->answer)->markdown(),
    replace: true
);
```
To:
```php
$this->stream(
    str($this->answer)->markdown(),
    el: 'answer',
    replace: true,
);
```

**Step 2: Update WithNotes.php**

In `modules/Holocron/Quest/Livewire/WithNotes.php`, change line 104-108 from:
```php
$this->stream(
    to: 'streamedAnswer',
    content: str($this->streamedAnswer)->markdown(),
    replace: true
);
```
To:
```php
$this->stream(
    str($this->streamedAnswer)->markdown(),
    el: 'streamedAnswer',
    replace: true,
);
```

**Step 3: Commit**

```bash
git add modules/Holocron/_Shared/Livewire/Chopper.php modules/Holocron/Quest/Livewire/WithNotes.php
git commit -m "fix: update stream() calls to Livewire 4 signature"
```

---

### Task 3: Migrate JS Hook and Remove `@livewireStyles`

**Files:**
- Modify: `resources/views/components/layouts/app.blade.php:15` (remove `@livewireStyles`)
- Modify: `resources/views/components/layouts/app.blade.php:81-91` (JS hook)

**Step 1: Remove `@livewireStyles`**

In `resources/views/components/layouts/app.blade.php`, remove line 15:
```blade
    @livewireStyles
```

Livewire 4 auto-injects styles. `@fluxAppearance` and `@fluxScripts` (already present) handle Flux UI.

**Step 2: Replace the JS request hook**

In the same file, replace lines 81-91:
```html
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.hook('request', ({ fail }) => {
                    fail(({ status, preventDefault }) => {
                        if (status === 419) {
                            window.location.reload()
                            preventDefault()
                        }
                    })
                })
            })
        </script>
```

With:
```html
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.interceptRequest(({ onError }) => {
                    onError(({ status, preventDefault }) => {
                        if (status === 419) {
                            window.location.reload()
                            preventDefault()
                        }
                    })
                })
            })
        </script>
```

**Step 3: Commit**

```bash
git add resources/views/components/layouts/app.blade.php
git commit -m "fix: migrate JS hook to interceptRequest and remove @livewireStyles"
```

---

### Task 4: Fix `wire:model` Modifier Changes

**Files:**
- Modify: `modules/Holocron/User/Views/settings.blade.php:6`
- Modify: `modules/Holocron/Grind/Views/workouts/components/set.blade.php:32,48`
- Modify: `modules/Holocron/Grind/Views/plans/components/exercise.blade.php:7,18,29,39`
- Modify: `modules/Holocron/Gear/Views/items/components/item.blade.php:22,31`

**Step 1: Fix `wire:model.blur` in settings**

In `modules/Holocron/User/Views/settings.blade.php` line 6, change:
```blade
<flux:input wire:model.blur="weight" />
```
To:
```blade
<flux:input wire:model.live.blur="weight" />
```

This preserves the v3 behavior where client-side state syncs immediately while the network request fires on blur.

**Step 2: Remove `.lazy` from workout set**

In `modules/Holocron/Grind/Views/workouts/components/set.blade.php`:

Line 32 — change `wire:model.lazy="weight"` to `wire:model="weight"`
Line 48 — change `wire:model.lazy="reps"` to `wire:model="reps"`

**Step 3: Remove `.lazy` from plan exercise**

In `modules/Holocron/Grind/Views/plans/components/exercise.blade.php`:

Line 7 — change `wire:model.lazy="sets"` to `wire:model="sets"`
Line 18 — change `wire:model.lazy="min_reps"` to `wire:model="min_reps"`
Line 29 — change `wire:model.lazy="max_reps"` to `wire:model="max_reps"`
Line 39 — change `wire:model.lazy="order"` to `wire:model="order"`

**Step 4: Remove `.lazy` from gear item**

In `modules/Holocron/Gear/Views/items/components/item.blade.php`:

Line 22 — change `wire:model.lazy="quantity"` to `wire:model="quantity"`
Line 31 — change `wire:model.lazy="quantity_per_day"` to `wire:model="quantity_per_day"`

**Step 5: Commit**

```bash
git add modules/Holocron/User/Views/settings.blade.php \
       modules/Holocron/Grind/Views/workouts/components/set.blade.php \
       modules/Holocron/Grind/Views/plans/components/exercise.blade.php \
       modules/Holocron/Gear/Views/items/components/item.blade.php
git commit -m "fix: update wire:model modifiers for Livewire 4 compatibility"
```

---

### Task 5: Convert Routes — Main App

**Files:**
- Modify: `routes/web.php:12,14,51,55`

**Step 1: Convert Livewire routes**

In `routes/web.php`, change these 4 lines from `Route::get` to `Route::livewire`:

Line 12:
```php
// Before
Route::get('/', Home::class)->name('pages.home');
// After
Route::livewire('/', Home::class)->name('pages.home');
```

Line 14:
```php
// Before
Route::get('/articles', Index::class)->name('articles.index');
// After
Route::livewire('/articles', Index::class)->name('articles.index');
```

Line 51:
```php
// Before
Route::get('/articles/{slug}', Show::class)
// After
Route::livewire('/articles/{slug}', Show::class)
```

Line 55:
```php
// Before
Route::get('/einmaleins', Einmaleins::class)->name('pages.einmaleins');
// After
Route::livewire('/einmaleins', Einmaleins::class)->name('pages.einmaleins');
```

Leave the closure routes (lines 16-47), `permanentRedirect` (line 49), and `Route::feeds()` (line 57) unchanged.

**Step 2: Run public-facing tests**

Run:
```bash
php artisan test tests/Feature/PagesTest.php tests/Feature/ArticlesTest.php
```

Expected: All tests pass.

**Step 3: Commit**

```bash
git add routes/web.php
git commit -m "refactor: convert main app routes to Route::livewire()"
```

---

### Task 6: Convert Routes — Holocron Modules

**Files:**
- Modify: `modules/Holocron/Bookmarks/Routes/web.php:8`
- Modify: `modules/Holocron/Dashboard/Routes/web.php:12-14`
- Modify: `modules/Holocron/Gear/Routes/web.php:9-12`
- Modify: `modules/Holocron/Grind/Routes/web.php:10-17`
- Modify: `modules/Holocron/Printer/Routes/web.php:9`
- Modify: `modules/Holocron/Quest/Routes/web.php:14-17`
- Modify: `modules/Holocron/School/Routes/web.php:12-15`
- Modify: `modules/Holocron/User/Routes/web.php:11,14-15`

**Step 1: Convert Bookmarks routes**

In `modules/Holocron/Bookmarks/Routes/web.php`:
```php
// Before
Route::get('/bookmarks', Modules\Holocron\Bookmarks\Livewire\Index::class)->name('bookmarks');
// After
Route::livewire('/bookmarks', Modules\Holocron\Bookmarks\Livewire\Index::class)->name('bookmarks');
```

**Step 2: Convert Dashboard routes**

In `modules/Holocron/Dashboard/Routes/web.php`, convert 3 Livewire routes. Keep the redirect closure on line 11 as `Route::get`:
```php
Route::get('/', fn () => redirect()->route('holocron.dashboard'));  // KEEP as Route::get — this is a closure
Route::livewire('/dashboard', Index::class)->name('dashboard');
Route::livewire('/chopper', Chopper::class)->name('chopper');
Route::livewire('/scrobbles', Scrobbles::class)->name('scrobbles');
```

**Step 3: Convert Gear routes**

In `modules/Holocron/Gear/Routes/web.php`, convert all 4:
```php
Route::livewire('/gear', Livewire\Index::class)->name('gear');
Route::livewire('/gear/categories', Livewire\Categories\Index::class)->name('gear.categories');
Route::livewire('/gear/items', Livewire\Items\Index::class)->name('gear.items');
Route::livewire('/gear/journeys/{journey}', Livewire\Journeys\Show::class)->name('gear.journeys.show');
```

**Step 4: Convert Grind routes**

In `modules/Holocron/Grind/Routes/web.php`, convert 7 Livewire routes. Keep the redirect on line 9 as `Route::get`:
```php
Route::get('/', fn () => to_route('holocron.grind.workouts.index'))->name('grind');  // KEEP — closure
Route::livewire('/exercises', Livewire\Exercises\Index::class)->name('grind.exercises.index');
Route::livewire('/exercises/{exercise}', Livewire\Exercises\Show::class)->name('grind.exercises.show');
Route::livewire('/plans', Livewire\Plans\Index::class)->name('grind.plans.index');
Route::livewire('/plans/{plan}', Livewire\Plans\Show::class)->name('grind.plans.show');
Route::livewire('/workouts', Livewire\Workouts\Index::class)->name('grind.workouts.index');
Route::livewire('/workouts/{workout}', Livewire\Workouts\Show::class)->name('grind.workouts.show');
```

**Step 5: Convert Printer routes**

In `modules/Holocron/Printer/Routes/web.php`:
```php
Route::livewire('/printer/queue', Index::class)->name('printer.queue');
```

**Step 6: Convert Quest routes**

In `modules/Holocron/Quest/Routes/web.php`, convert 4 Livewire routes. Keep the signed controller route unchanged:
```php
Route::get('/holocron/quests/complete', CompleteController::class)  // KEEP — controller
    ->name('holocron.quests.complete')
    ->middleware('signed');

Route::middleware(['web', 'auth'])->name('holocron.')->prefix('holocron')->group(function () {
    Route::livewire('/quests', Livewire\Index::class)->name('quests');
    Route::livewire('/quests/daily', Livewire\DailyQuest::class)->name('quests.daily');
    Route::livewire('/quests/recurring', Livewire\RecurringQuests::class)->name('quests.recurring');
    Route::livewire('/quests/{quest}', Livewire\Show::class)->name('quests.show');
});
```

**Step 7: Convert School routes**

In `modules/Holocron/School/Routes/web.php`:
```php
Route::livewire('/information', Information::class)->name('information');
Route::livewire('/vocabulary', Vocabulary::class)->name('vocabulary.overview');
Route::livewire('/vocabulary/test/{test}', VocabularyTest::class)->name('vocabulary.test');
Route::livewire('/vocabulary/print-test/{test}', VocabularyPrintTest::class)->name('vocabulary.print-test');
```

**Step 8: Convert User routes**

In `modules/Holocron/User/Routes/web.php`:
```php
Route::middleware('web')->name('holocron.')->prefix('holocron')->group(function () {
    Route::livewire('login', Login::class)->name('login');

    Route::middleware('auth')->group(function () {
        Route::livewire('/experience', Experience::class)->name('experience');
        Route::livewire('/settings', Settings::class)->name('settings');
    });
});
```

**Step 9: Commit**

```bash
git add modules/Holocron/Bookmarks/Routes/web.php \
       modules/Holocron/Dashboard/Routes/web.php \
       modules/Holocron/Gear/Routes/web.php \
       modules/Holocron/Grind/Routes/web.php \
       modules/Holocron/Printer/Routes/web.php \
       modules/Holocron/Quest/Routes/web.php \
       modules/Holocron/School/Routes/web.php \
       modules/Holocron/User/Routes/web.php
git commit -m "refactor: convert Holocron module routes to Route::livewire()"
```

---

### Task 7: Run Pint and Full Test Suite

**Step 1: Run Pint**

Run:
```bash
vendor/bin/pint --dirty
```

Expected: Any formatting issues are auto-fixed.

**Step 2: Run the full test suite**

Run:
```bash
php artisan test
```

Expected: All tests pass. If any fail, debug and fix before proceeding.

**Step 3: Commit any Pint fixes**

```bash
git add -A
git commit -m "style: apply pint formatting after Livewire 4 migration"
```

(Only if Pint made changes.)

---

### Task 8: Smoke Test in Browser

**Step 1: Verify public pages load**

Visit the following URLs in a browser and confirm no errors:
- Homepage (`/`)
- Articles index (`/articles`)
- An article detail page
- Einmaleins page (`/einmaleins`)

**Step 2: Verify Holocron pages load**

Log in and visit:
- Dashboard (`/holocron/dashboard`)
- Quests (`/holocron/quests`)
- A quest detail page (test the AI chat streaming)
- Chopper (`/holocron/chopper`) (test streaming)
- Workouts, Plans, Exercises pages
- Settings page (test the weight input blur behavior)

**Step 3: Check browser console**

Confirm no JavaScript errors, particularly:
- No "Alpine has already been loaded" warnings
- No 404s for old `/livewire/update` endpoint
- No `Livewire.hook` deprecation warnings
