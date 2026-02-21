# Chopper Smart Autoscroll Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Add smart autoscroll to the Chopper chat UI that follows streaming content but respects manual scroll position.

**Architecture:** Replace the broken `x-effect` scroll approach with an inline Alpine component using `MutationObserver` to detect all DOM changes (Livewire re-renders + `wire:stream` mutations). Add a Livewire `dispatch('message-sent')` so Alpine force-scrolls when the user sends a message.

**Tech Stack:** Alpine.js (inline `x-data`), Livewire 3 `dispatch()`, MutationObserver API

---

### Task 1: Add `message-sent` dispatch to Livewire `send()` and write test

**Files:**
- Modify: `modules/Holocron/_Shared/Livewire/Chopper.php:29-43`
- Test: `modules/Holocron/_Shared/Tests/ChopperTest.php`

**Step 1: Write the failing test**

Add to end of `modules/Holocron/_Shared/Tests/ChopperTest.php`:

```php
it('dispatches message-sent event when sending a message', function () {
    $user = User::factory()->create(['email' => 'timkley@gmail.com']);

    Livewire::actingAs($user)
        ->test(Chopper::class)
        ->set('message', 'Hallo Chopper!')
        ->call('send')
        ->assertDispatched('message-sent');
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --filter="dispatches message-sent event"`
Expected: FAIL — `message-sent` is not dispatched yet.

**Step 3: Add `dispatch('message-sent')` to `send()`**

In `modules/Holocron/_Shared/Livewire/Chopper.php`, add one line inside `send()`, after line 40 (`$this->messages[] = ...`) and before line 42 (`$this->js(...)`):

```php
$this->dispatch('message-sent');
```

The full `send()` method becomes:

```php
public function send(): void
{
    if (empty(mb_trim($this->message))) {
        return;
    }

    $userMessage = $this->message;
    $this->message = '';
    $this->streamedResponse = '';
    $this->isStreaming = true;

    $this->messages[] = ['role' => 'user', 'content' => $userMessage];

    $this->dispatch('message-sent');

    $this->js('$wire.ask('.json_encode($userMessage, JSON_THROW_ON_ERROR).')');
}
```

**Step 4: Run test to verify it passes**

Run: `php artisan test --filter="dispatches message-sent event"`
Expected: PASS

**Step 5: Run all Chopper tests**

Run: `php artisan test modules/Holocron/_Shared/Tests/ChopperTest.php`
Expected: All tests PASS (existing tests unaffected).

**Step 6: Commit**

```bash
git add modules/Holocron/_Shared/Livewire/Chopper.php modules/Holocron/_Shared/Tests/ChopperTest.php
git commit -m "feat: dispatch message-sent event from Chopper send()"
```

---

### Task 2: Replace broken `x-effect` with smart Alpine autoscroll

**Files:**
- Modify: `modules/Holocron/_Shared/Views/chopper.blade.php:51-56`

**Step 1: Replace the `x-data` / `x-effect` on `#chat-messages`**

In `modules/Holocron/_Shared/Views/chopper.blade.php`, replace lines 51-56:

```blade
        <div
            class="flex-1 space-y-4 overflow-y-auto pb-4"
            id="chat-messages"
            x-data
            x-effect="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
        >
```

With:

```blade
        <div
            class="flex-1 space-y-4 overflow-y-auto pb-4"
            id="chat-messages"
            x-data="{
                shouldAutoScroll: true,
                observer: null,
                scrollToBottom() {
                    this.$el.scrollTop = this.$el.scrollHeight
                },
                isNearBottom() {
                    return this.$el.scrollHeight - this.$el.scrollTop - this.$el.clientHeight < 50
                },
                init() {
                    this.observer = new MutationObserver(() => {
                        if (this.shouldAutoScroll) {
                            this.scrollToBottom()
                        }
                    })
                    this.observer.observe(this.$el, { childList: true, subtree: true, characterData: true })
                },
                destroy() {
                    this.observer?.disconnect()
                }
            }"
            @scroll="shouldAutoScroll = isNearBottom()"
            @message-sent.window="shouldAutoScroll = true; scrollToBottom()"
        >
```

**Step 2: Run all Chopper tests to verify nothing broke**

Run: `php artisan test modules/Holocron/_Shared/Tests/ChopperTest.php`
Expected: All tests PASS.

**Step 3: Run Pint**

Run: `vendor/bin/pint --dirty`

**Step 4: Commit**

```bash
git add modules/Holocron/_Shared/Views/chopper.blade.php
git commit -m "feat: add smart autoscroll to Chopper chat UI"
```

---

### Task 3: Manual verification

**Step 1: Verify in browser**

Open the Chopper page. Verify:
1. Sending a message scrolls to the bottom.
2. As the assistant streams a response, the chat scrolls to follow it.
3. Scrolling up mid-stream stops the auto-scroll.
4. Scrolling back to the bottom resumes auto-scroll.
5. Loading an existing conversation shows messages without scroll issues.
