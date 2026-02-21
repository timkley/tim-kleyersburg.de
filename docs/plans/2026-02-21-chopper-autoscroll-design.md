# Chopper Smart Autoscroll Design

## Problem

The Chopper chat UI doesn't reliably auto-scroll when new messages arrive or during streaming. The current `x-effect` approach on line 55 of `chopper.blade.php` doesn't fire during `wire:stream` DOM mutations, and it always forces scroll even when the user has scrolled up to read earlier messages.

## Solution

Replace the current `x-data`/`x-effect` on `#chat-messages` with an inline Alpine component that uses a `MutationObserver` to detect all DOM changes and scrolls smartly.

### Behavior

- **Smart scroll:** Only auto-scroll when the user is within ~50px of the bottom.
- **Force scroll on send:** When the user sends a message, always scroll to bottom (sending = intent to see the response).
- **Works with `wire:stream`:** MutationObserver fires on direct DOM mutations, not just Livewire re-renders.

### Changes

**`chopper.blade.php`** — Replace `x-data` / `x-effect` on `#chat-messages` with:

```html
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
```

**`Chopper.php`** — Add `$this->dispatch('message-sent')` in the `send()` method.

### Test

Add a test verifying that the `message-sent` event is dispatched when a message is sent.

### Not Changed

- Streaming logic in `ask()`
- Conversation sidebar
- Existing tests (all remain valid)
