# Livewire 3 → 4 Migration Design

## Context

- **Current:** Livewire 3.7.6, Flux UI 2.11.1, PHP 8.4, Laravel 12
- **Target:** Livewire 4.x
- **Approach:** Big bang — single pass, all changes at once
- **Scope:** Fix breaking changes, convert routes to `Route::livewire()`, evaluate `@island` adoption

## 1. Dependency Updates

**composer.json:**

| Package | Current | Target |
|---------|---------|--------|
| `livewire/livewire` | `^3.5.18` | `^4.0` |
| `livewire/flux` | `^2.0` | `^2.0` (update to latest) |
| `livewire/flux-pro` | `^2.0` | `^2.0` (update to latest) |

After updating: `php artisan optimize:clear` and `npm run build`.

No other dependency changes needed. PHP 8.4, Laravel 12, Pulse 1.5 all support Livewire 4.

## 2. Breaking Change Fixes

### A. `$this->stream()` signature (2 files)

The `to:` parameter is renamed to `el:` and content becomes the first positional argument.

**`modules/Holocron/_Shared/Livewire/Chopper.php:69`**
```php
// Before
$this->stream(to: 'answer', content: str($this->answer)->markdown(), replace: true);
// After
$this->stream(str($this->answer)->markdown(), el: 'answer', replace: true);
```

**`modules/Holocron/Quest/Livewire/WithNotes.php:104`**
```php
// Before
$this->stream(to: 'streamedAnswer', content: str($this->streamedAnswer)->markdown(), replace: true);
// After
$this->stream(str($this->streamedAnswer)->markdown(), el: 'streamedAnswer', replace: true);
```

### B. JS hook migration (1 file)

**`resources/views/components/layouts/app.blade.php:82`**

```js
// Before
Livewire.hook('request', ({ fail }) => {
    fail(({ status, preventDefault }) => {
        if (status === 419) {
            window.location.reload()
            preventDefault()
        }
    })
})

// After
Livewire.interceptRequest(({ onError }) => {
    onError(({ status, preventDefault }) => {
        if (status === 419) {
            window.location.reload()
            preventDefault()
        }
    })
})
```

### C. `wire:model.blur` behavior (1 file)

**`modules/Holocron/User/Views/settings.blade.php:6`**

Change `wire:model.blur` to `wire:model.live.blur` to preserve v3 behavior (immediate client-side sync, network request on blur).

### D. `wire:model.lazy` cleanup (8 occurrences, 3 files)

Remove `.lazy` modifier — it was already a no-op in v3 and may not be recognized in v4:

- `modules/Holocron/Grind/Views/workouts/components/set.blade.php` (2x)
- `modules/Holocron/Grind/Views/plans/components/exercise.blade.php` (4x)
- `modules/Holocron/Gear/Views/items/components/item.blade.php` (2x)

### E. `@livewireStyles` removal (1 file)

**`resources/views/components/layouts/app.blade.php:15`**

Remove `@livewireStyles` — Livewire 4 auto-injects styles.

## 3. Route Conversion (~30 routes, 9 files)

Convert `Route::get('/path', Component::class)` to `Route::livewire('/path', Component::class)` for all Livewire full-page components. Non-Livewire routes (closures, controllers, redirects) remain unchanged.

**Files:**
- `routes/web.php` (4 Livewire routes)
- `modules/Holocron/Bookmarks/Routes/web.php` (1)
- `modules/Holocron/Dashboard/Routes/web.php` (3)
- `modules/Holocron/Gear/Routes/web.php` (4)
- `modules/Holocron/Grind/Routes/web.php` (7)
- `modules/Holocron/Printer/Routes/web.php` (1)
- `modules/Holocron/Quest/Routes/web.php` (4)
- `modules/Holocron/School/Routes/web.php` (4)
- `modules/Holocron/User/Routes/web.php` (3)

## 4. `@island` Adoption

Evaluated but **deferred** to a follow-up. Current candidates (LastScrobble, Apod, charts) are already separate `#[Lazy]` components, and `@island` is designed for isolating sections *within* a component. No natural fit for the current architecture.

## 5. Test Verification

Run the full test suite after all changes:
- `tests/Feature/PrinterStatusTest.php`
- `tests/Feature/QuestAttachmentTest.php`
- All 13 Holocron module test files using `Livewire::test()`
