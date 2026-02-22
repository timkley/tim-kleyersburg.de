# Chopper Image Attachments Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Add image attachment support to Chopper so users can send images alongside chat messages for AI analysis.

**Architecture:** Livewire `WithFileUploads` handles upload. Images stored to the `local` disk at `chopper-attachments/`. Passed as `Image::fromStorage()` to the Laravel AI SDK's `stream()` method. The SDK auto-persists path references in the `attachments` DB column. UI uses `flux:composer` with Alpine.js for drag-drop and paste.

**Tech Stack:** Laravel AI SDK (`laravel/ai`), Livewire 3 `WithFileUploads`, Flux UI Pro `flux:composer`, Alpine.js

---

### Task 1: Backend - File upload and storage in send()

**Files:**
- Modify: `modules/Holocron/_Shared/Livewire/Chopper.php`
- Test: `modules/Holocron/_Shared/Tests/ChopperTest.php`

**Step 1: Write the failing tests**

Add to `modules/Holocron/_Shared/Tests/ChopperTest.php`:

```php
it('can send a message with image attachments', function () {
    Storage::fake('local');
    ChopperAgent::fake(['Ich sehe das Bild!']);

    $user = User::factory()->create(['email' => 'timkley@gmail.com']);
    $file = UploadedFile::fake()->image('photo.jpg');

    Livewire::actingAs($user)
        ->test(Chopper::class)
        ->set('message', 'Was siehst du?')
        ->set('attachments', [$file])
        ->call('send')
        ->assertSet('message', '')
        ->assertSet('attachments', [])
        ->assertSet('isStreaming', true);

    Storage::disk('local')->assertDirectoryExists('chopper-attachments');
});

it('validates attachments are images', function () {
    $user = User::factory()->create(['email' => 'timkley@gmail.com']);
    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    Livewire::actingAs($user)
        ->test(Chopper::class)
        ->set('message', 'Check this')
        ->set('attachments', [$file])
        ->call('send')
        ->assertHasErrors('attachments.0');
});

it('validates max 5 attachments', function () {
    $user = User::factory()->create(['email' => 'timkley@gmail.com']);
    $files = array_map(fn () => UploadedFile::fake()->image('photo.jpg'), range(1, 6));

    Livewire::actingAs($user)
        ->test(Chopper::class)
        ->set('message', 'Too many')
        ->set('attachments', $files)
        ->call('send')
        ->assertHasErrors('attachments');
});

it('passes attachments to the agent when asking', function () {
    Storage::fake('local');
    ChopperAgent::fake(['Ich sehe ein Bild!']);

    $user = User::factory()->create(['email' => 'timkley@gmail.com']);
    $file = UploadedFile::fake()->image('photo.jpg');

    $component = Livewire::actingAs($user)
        ->test(Chopper::class)
        ->set('message', 'Beschreibe das Bild')
        ->set('attachments', [$file])
        ->call('send');

    // The stored path is passed to ask() via JS, so we call ask() directly with paths
    $storedFiles = Storage::disk('local')->allFiles('chopper-attachments');
    expect($storedFiles)->toHaveCount(1);

    $component->call('ask', 'Beschreibe das Bild', $storedFiles);

    ChopperAgent::assertPrompted('Beschreibe das Bild');
});

it('sends without attachments still works', function () {
    ChopperAgent::fake(['Hallo!']);

    $user = User::factory()->create(['email' => 'timkley@gmail.com']);

    Livewire::actingAs($user)
        ->test(Chopper::class)
        ->set('message', 'Hallo Chopper!')
        ->call('send')
        ->assertSet('message', '')
        ->assertSet('isStreaming', true)
        ->assertCount('messages', 1);
});
```

Add these imports at the top of the test file:

```php
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
```

**Step 2: Run tests to verify they fail**

Run: `php artisan test modules/Holocron/_Shared/Tests/ChopperTest.php`
Expected: New tests FAIL (no `attachments` property, no validation, no storage)

**Step 3: Implement the backend changes**

Modify `modules/Holocron/_Shared/Livewire/Chopper.php`:

1. Add `use Livewire\WithFileUploads;` import
2. Add `use Laravel\Ai\Files\Image as AiImage;` import
3. Add trait `use WithFileUploads;` inside the class
4. Add property: `public array $attachments = [];`
5. Update `send()` to validate, store files, and pass paths to `ask()`
6. Update `ask()` to accept `array $storagePaths = []` parameter and build AI attachments

Updated `send()`:

```php
public function send(): void
{
    if (empty(mb_trim($this->message)) && empty($this->attachments)) {
        return;
    }

    $this->validate([
        'attachments' => ['array', 'max:5'],
        'attachments.*' => ['image', 'max:10240'],
    ]);

    $storagePaths = [];
    foreach ($this->attachments as $file) {
        $storagePaths[] = $file->store('chopper-attachments', 'local');
    }

    $userMessage = $this->message;
    $this->message = '';
    $this->attachments = [];
    $this->streamedResponse = '';
    $this->isStreaming = true;

    $this->messages[] = [
        'role' => 'user',
        'content' => $userMessage,
        'attachments' => $storagePaths,
    ];

    $this->dispatch('message-sent');

    $this->js('$wire.ask(' . json_encode($userMessage, JSON_THROW_ON_ERROR) . ', ' . json_encode($storagePaths, JSON_THROW_ON_ERROR) . ')');
}
```

Updated `ask()`:

```php
public function ask(string $userMessage, array $storagePaths = []): void
{
    $agent = new ChopperAgent;

    $attachments = array_map(
        fn (string $path) => AiImage::fromStorage($path),
        $storagePaths,
    );

    if ($this->conversationId) {
        $stream = $agent->continue($this->conversationId, auth()->user())->stream($userMessage, $attachments);
    } else {
        $stream = $agent->forUser(auth()->user())->stream($userMessage, $attachments);
    }

    foreach ($stream as $event) {
        if (! $event instanceof TextDelta) {
            continue;
        }

        $this->streamedResponse .= $event->delta;

        $this->stream(
            to: 'assistant-response',
            content: str($this->streamedResponse)->markdown(),
            replace: true,
        );
    }

    $this->messages[] = ['role' => 'assistant', 'content' => $this->streamedResponse];

    $stream->then(function (StreamedAgentResponse $response): void {
        if (! $this->conversationId && $response->conversationId) {
            $this->conversationId = $response->conversationId;

            $this->js(
                "history.replaceState({}, '', '" . route('holocron.chopper', $response->conversationId) . "')"
            );
        }
    });

    $this->isStreaming = false;
    $this->streamedResponse = '';
}
```

**Step 4: Run tests to verify they pass**

Run: `php artisan test modules/Holocron/_Shared/Tests/ChopperTest.php`
Expected: All tests PASS

**Step 5: Run Pint**

Run: `vendor/bin/pint --dirty`

**Step 6: Commit**

```bash
git add modules/Holocron/_Shared/Livewire/Chopper.php modules/Holocron/_Shared/Tests/ChopperTest.php
git commit -m "feat(chopper): add image attachment upload and storage backend"
```

---

### Task 2: Backend - Load attachments from conversation history

**Files:**
- Modify: `modules/Holocron/_Shared/Livewire/Chopper.php` (the `loadMessages()` method)
- Test: `modules/Holocron/_Shared/Tests/ChopperTest.php`

**Step 1: Write the failing test**

Add to `modules/Holocron/_Shared/Tests/ChopperTest.php`:

```php
it('loads attachments from conversation history', function () {
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
        'content' => 'Look at this image',
        'attachments' => json_encode([
            ['type' => 'stored-image', 'path' => 'chopper-attachments/test-photo.jpg', 'disk' => 'local'],
        ]),
        'tool_calls' => '[]',
        'tool_results' => '[]',
        'usage' => '{}',
        'meta' => '{}',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('agent_conversation_messages')->insert([
        'id' => (string) Str::uuid(),
        'conversation_id' => $conversationId,
        'user_id' => $user->id,
        'agent' => 'chopper',
        'role' => 'assistant',
        'content' => 'I see a photo!',
        'attachments' => '[]',
        'tool_calls' => '[]',
        'tool_results' => '[]',
        'usage' => '{}',
        'meta' => '{}',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $component = Livewire::actingAs($user)
        ->test(Chopper::class, ['conversationId' => $conversationId]);

    $messages = $component->get('messages');
    expect($messages[0])->toHaveKey('attachments')
        ->and($messages[0]['attachments'])->toBe(['chopper-attachments/test-photo.jpg'])
        ->and($messages[1]['attachments'])->toBe([]);
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test modules/Holocron/_Shared/Tests/ChopperTest.php --filter="loads attachments"`
Expected: FAIL

**Step 3: Update loadMessages()**

In `modules/Holocron/_Shared/Livewire/Chopper.php`, update `loadMessages()`:

```php
protected function loadMessages(): void
{
    if (! $this->conversationId) {
        return;
    }

    $messages = DB::table('agent_conversation_messages')
        ->where('conversation_id', $this->conversationId)
        ->orderBy('created_at')
        ->get();

    $this->messages = $messages
        ->filter(fn (object $msg) => in_array($msg->role, ['user', 'assistant']))
        ->map(function (object $msg) {
            $attachments = collect(json_decode($msg->attachments, true))
                ->filter(fn (array $a) => ($a['type'] ?? '') === 'stored-image')
                ->pluck('path')
                ->values()
                ->all();

            return [
                'role' => $msg->role,
                'content' => $msg->content,
                'attachments' => $attachments,
            ];
        })
        ->values()
        ->all();
}
```

**Step 4: Run test to verify it passes**

Run: `php artisan test modules/Holocron/_Shared/Tests/ChopperTest.php --filter="loads attachments"`
Expected: PASS

**Step 5: Run Pint and commit**

```bash
vendor/bin/pint --dirty
git add modules/Holocron/_Shared/Livewire/Chopper.php modules/Holocron/_Shared/Tests/ChopperTest.php
git commit -m "feat(chopper): load image attachments from conversation history"
```

---

### Task 3: UI - Replace input with flux:composer and add attachment display

**Files:**
- Modify: `modules/Holocron/_Shared/Views/chopper.blade.php`

**Step 1: Update the input area**

Replace the `{{-- Input --}}` section (lines 128-136) with:

```blade
{{-- Input --}}
<form
    wire:submit="send"
    class="mt-4"
    x-data="{
        dragOver: false,
        handleDrop(e) {
            this.dragOver = false;
            const files = [...e.dataTransfer.files].filter(f => f.type.startsWith('image/'));
            if (files.length) {
                $wire.uploadMultiple('attachments', files);
            }
        },
        handlePaste(e) {
            const items = [...(e.clipboardData?.items || [])];
            const imageFiles = items
                .filter(item => item.type.startsWith('image/'))
                .map(item => item.getAsFile())
                .filter(Boolean);
            if (imageFiles.length) {
                e.preventDefault();
                $wire.uploadMultiple('attachments', imageFiles);
            }
        },
    }"
    @dragover.prevent="dragOver = true"
    @dragleave.prevent="dragOver = false"
    @drop.prevent="handleDrop($event)"
    @paste="handlePaste($event)"
>
    <flux:composer
        wire:model="message"
        submit="enter"
        placeholder="Nachricht an Chopper..."
        :class="dragOver ? 'ring-2 ring-accent!' : ''"
    >
        <x-slot:header>
            @if ($attachments)
                <div class="flex flex-wrap gap-2 pb-1">
                    @foreach ($attachments as $index => $attachment)
                        <div wire:key="preview-{{ $index }}" class="group relative">
                            <img
                                src="{{ $attachment->temporaryUrl() }}"
                                class="size-16 rounded-lg object-cover"
                                alt="Anhang {{ $index + 1 }}"
                            />
                            <button
                                type="button"
                                wire:click="removeAttachment({{ $index }})"
                                class="absolute -right-1 -top-1 hidden size-5 items-center justify-center rounded-full bg-zinc-800 text-white group-hover:flex dark:bg-zinc-200 dark:text-zinc-900"
                            >
                                <flux:icon.x-mark class="size-3" />
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-slot:header>

        <x-slot:actionsLeading>
            <flux:button icon="photo" size="sm" variant="subtle" x-on:click="$refs.fileInput.click()" />
            <input
                x-ref="fileInput"
                type="file"
                wire:model="attachments"
                multiple
                accept="image/*"
                class="hidden"
            />
        </x-slot:actionsLeading>

        <x-slot:actionsTrailing>
            <flux:button
                type="submit"
                icon="paper-airplane"
                size="sm"
                variant="primary"
                wire:loading.attr="disabled"
            />
        </x-slot:actionsTrailing>
    </flux:composer>
</flux:composer>
</form>
```

**Step 2: Update message display to show attachments**

Replace the user message rendering section inside the `@foreach` loop. The `@else` block (line 110-111) becomes:

```blade
@else
    @if (! empty($msg['attachments']))
        <div class="mb-2 flex flex-wrap gap-1">
            @foreach ($msg['attachments'] as $path)
                <img
                    src="{{ Storage::disk('local')->url($path) }}"
                    class="max-h-32 rounded-lg"
                    alt="Anhang"
                />
            @endforeach
        </div>
    @endif
    {{ $msg['content'] }}
@endif
```

**Step 3: Add removeAttachment method to Chopper.php**

Add to `modules/Holocron/_Shared/Livewire/Chopper.php`:

```php
public function removeAttachment(int $index): void
{
    $attachments = $this->attachments;
    array_splice($attachments, $index, 1);
    $this->attachments = array_values($attachments);
}
```

**Step 4: Test manually in the browser**

Verify:
- The composer component renders with textarea, photo button, and send button
- Clicking the photo icon opens a file picker (images only)
- Selected images show as thumbnails in the header
- Clicking X removes a staged image
- Drag-and-drop onto the composer shows images
- Cmd+V with an image on clipboard adds it
- Sending with images works and clears the staging area
- Messages with images display thumbnails

**Step 5: Run Pint and commit**

```bash
vendor/bin/pint --dirty
git add modules/Holocron/_Shared/Views/chopper.blade.php modules/Holocron/_Shared/Livewire/Chopper.php
git commit -m "feat(chopper): add attachment UI with composer, drag-drop, and paste"
```

---

### Task 4: Run full test suite and fix any issues

**Step 1: Run the Chopper tests**

Run: `php artisan test modules/Holocron/_Shared/Tests/ChopperTest.php`
Expected: All PASS

**Step 2: Run Pint**

Run: `vendor/bin/pint --dirty`

**Step 3: Ask user if they want to run the full test suite**

If yes: `php artisan test`

**Step 4: Final commit if any fixes were needed**

```bash
git add -A
git commit -m "fix(chopper): address test failures from attachment feature"
```
