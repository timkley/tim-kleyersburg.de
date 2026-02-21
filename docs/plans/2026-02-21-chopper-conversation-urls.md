# Chopper Conversation URLs Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Give each Chopper conversation a unique URL so page reloads restore the conversation instead of showing the empty state.

**Architecture:** Add an optional `{conversationId?}` route parameter. The Livewire component's `mount()` accepts it, validates ownership, and loads messages. Sidebar links use `wire:navigate` for SPA-like navigation. After a new conversation is created during streaming, `history.replaceState` updates the URL without a jarring re-mount.

**Tech Stack:** Laravel routing, Livewire 3 `mount()` + `wire:navigate`, Alpine.js `history.replaceState`

---

### Task 1: Update Route to Accept Optional Conversation ID

**Files:**
- Modify: `modules/Holocron/Dashboard/Routes/web.php:13`

**Step 1: Update the route**

Change the Chopper route to accept an optional `{conversationId?}` parameter:

```php
Route::livewire('/chopper/{conversationId?}', Chopper::class)->name('chopper');
```

**Step 2: Verify routes compile**

Run: `php artisan route:list --path=holocron/chopper`
Expected: Route shows `/holocron/chopper/{conversationId?}`

**Step 3: Commit**

```bash
git add modules/Holocron/Dashboard/Routes/web.php
git commit -m "feat: add optional conversationId parameter to Chopper route"
```

---

### Task 2: Add `mount()` with Ownership Validation

**Files:**
- Modify: `modules/Holocron/_Shared/Livewire/Chopper.php`

**Step 1: Write the failing test**

Add to `modules/Holocron/_Shared/Tests/ChopperTest.php`:

```php
it('loads a conversation by route parameter', function () {
    $user = User::factory()->create(['email' => 'timkley@gmail.com']);

    $conversationId = (string) Str::uuid();
    DB::table('agent_conversations')->insert([
        'id' => $conversationId,
        'user_id' => $user->id,
        'title' => 'Test Conversation',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('agent_conversation_messages')->insert([
        'id' => (string) Str::uuid(),
        'conversation_id' => $conversationId,
        'user_id' => $user->id,
        'agent' => 'chopper',
        'role' => 'user',
        'content' => 'Hello',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    actingAs($user)
        ->get(route('holocron.chopper', $conversationId))
        ->assertSuccessful()
        ->assertSeeLivewire(Chopper::class);
});

it('returns 404 for another users conversation', function () {
    $user = User::factory()->create(['email' => 'timkley@gmail.com']);
    $otherUser = User::factory()->create();

    $conversationId = (string) Str::uuid();
    DB::table('agent_conversations')->insert([
        'id' => $conversationId,
        'user_id' => $otherUser->id,
        'title' => 'Private Conversation',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    actingAs($user)
        ->get(route('holocron.chopper', $conversationId))
        ->assertNotFound();
});

it('returns 404 for nonexistent conversation', function () {
    $user = User::factory()->create(['email' => 'timkley@gmail.com']);

    actingAs($user)
        ->get(route('holocron.chopper', 'nonexistent-id'))
        ->assertNotFound();
});
```

Add the necessary imports at the top of the test file:

```php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
```

**Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Modules/Holocron/_Shared/Tests/ChopperTest.php --filter="loads a conversation|returns 404"`
Expected: Tests fail (no `mount()` method yet)

**Step 3: Implement `mount()` on the Chopper component**

Add the `mount()` method to `Chopper.php`:

```php
public function mount(?string $conversationId = null): void
{
    if ($conversationId) {
        $exists = DB::table('agent_conversations')
            ->where('id', $conversationId)
            ->where('user_id', auth()->id())
            ->exists();

        if (! $exists) {
            abort(404);
        }

        $this->conversationId = $conversationId;
        $this->loadMessages();
    }
}
```

**Step 4: Run tests to verify they pass**

Run: `php artisan test tests/Modules/Holocron/_Shared/Tests/ChopperTest.php`
Expected: All tests pass (including existing ones)

**Step 5: Commit**

```bash
git add modules/Holocron/_Shared/Livewire/Chopper.php modules/Holocron/_Shared/Tests/ChopperTest.php
git commit -m "feat: add mount() with ownership validation to Chopper"
```

---

### Task 3: Convert Sidebar to `wire:navigate` Links

**Files:**
- Modify: `modules/Holocron/_Shared/Views/chopper.blade.php`
- Modify: `modules/Holocron/_Shared/Livewire/Chopper.php` (remove `selectConversation` and `newConversation` methods)

**Step 1: Update the desktop sidebar**

Replace the `wire:click` buttons with `<a>` tags using `wire:navigate`:

Desktop "New Conversation" button — replace:
```blade
<flux:button wire:click="newConversation" variant="primary" class="w-full">
    Neues Gespräch
</flux:button>
```
With:
```blade
<flux:button :href="route('holocron.chopper')" variant="primary" class="w-full" wire:navigate>
    Neues Gespräch
</flux:button>
```

Desktop conversation list — replace:
```blade
<button
    wire:click="selectConversation('{{ $conv->id }}')"
    wire:key="conv-{{ $conv->id }}"
    @class([...])
>
```
With:
```blade
<a
    href="{{ route('holocron.chopper', $conv->id) }}"
    wire:navigate
    wire:key="conv-{{ $conv->id }}"
    @class([...])
>
```
And change the closing `</button>` to `</a>`.

**Step 2: Update the mobile dropdown**

Mobile "New Conversation" — replace:
```blade
<flux:menu.item wire:click="newConversation" icon="plus">
```
With:
```blade
<flux:menu.item :href="route('holocron.chopper')" icon="plus" wire:navigate>
```

Mobile conversation items — replace:
```blade
<flux:menu.item
    wire:click="selectConversation('{{ $conv->id }}')"
    wire:key="conv-mobile-{{ $conv->id }}"
>
```
With:
```blade
<flux:menu.item
    :href="route('holocron.chopper', $conv->id)"
    wire:key="conv-mobile-{{ $conv->id }}"
    wire:navigate
>
```

**Step 3: Remove `selectConversation()` and `newConversation()` from Chopper.php**

These methods are no longer called. Delete them entirely.

**Step 4: Update existing tests**

The test `it('can start a new conversation')` calls `->call('newConversation')` which no longer exists. Remove this test since navigation is now handled by URL routing, not Livewire actions.

**Step 5: Run all tests**

Run: `php artisan test tests/Modules/Holocron/_Shared/Tests/ChopperTest.php`
Expected: All tests pass

**Step 6: Commit**

```bash
git add modules/Holocron/_Shared/Views/chopper.blade.php modules/Holocron/_Shared/Livewire/Chopper.php modules/Holocron/_Shared/Tests/ChopperTest.php
git commit -m "feat: convert Chopper sidebar to wire:navigate links"
```

---

### Task 4: Update URL After New Conversation Created

**Files:**
- Modify: `modules/Holocron/_Shared/Livewire/Chopper.php` (the `ask()` method)

**Step 1: Update `ask()` to push the URL after new conversation creation**

In the `stream->then()` callback, after setting `$this->conversationId`, use `$this->js()` to update the browser URL via `history.replaceState`:

```php
$stream->then(function (StreamedAgentResponse $response): void {
    if (! $this->conversationId && $response->conversationId) {
        $this->conversationId = $response->conversationId;

        $this->js(
            "history.replaceState({}, '', '".route('holocron.chopper', $response->conversationId)."')"
        );
    }
});
```

This updates the address bar without a re-mount, so the streaming response stays visible.

**Step 2: Run all tests**

Run: `php artisan test tests/Modules/Holocron/_Shared/Tests/ChopperTest.php`
Expected: All tests pass

**Step 3: Commit**

```bash
git add modules/Holocron/_Shared/Livewire/Chopper.php
git commit -m "feat: update browser URL after new Chopper conversation created"
```

---

### Task 5: Run Full Test Suite and Fix Pint

**Step 1: Run Pint**

Run: `vendor/bin/pint --dirty`

**Step 2: Run full test suite**

Run: `php artisan test`
Expected: All tests pass

**Step 3: Commit any formatting fixes**

```bash
git add -A
git commit -m "style: fix formatting"
```
