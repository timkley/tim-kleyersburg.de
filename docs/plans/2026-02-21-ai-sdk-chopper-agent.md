# AI SDK Chopper Agent Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Replace the current Chopper RAG Q&A with a full Laravel AI SDK agent that has tools for reading/writing quests and notes, with a persistent multi-turn chat UI.

**Architecture:** Install `laravel/ai`, create a `ChopperAgent` class with `RemembersConversations` for persistent chat history and 7 custom tools (4 read, 3 write). Migrate all 6 existing Prism call sites to AI SDK anonymous agents. Rewrite the Chopper Livewire component as a chat interface with conversation sidebar.

**Tech Stack:** Laravel AI SDK (`laravel/ai`), Livewire 4, Flux UI Pro, OpenRouter + Gemini 2.5 Flash

---

## Phase 1: Package Setup

### Task 1: Install Laravel AI SDK

**Step 1: Install the package**

Run: `composer require laravel/ai`

This will automatically upgrade `prism-php/prism` from `^0.86` to `^0.99` (transitive dependency). Existing Prism code will break — that's expected and handled in Phase 2.

**Step 2: Publish config and migrations**

Run: `php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"`

This creates `config/ai.php` and the conversation storage migration.

**Step 3: Run migrations**

Run: `php artisan migrate`

Creates the `ai_conversations` and related tables.

**Step 4: Verify installation**

Run: `php artisan list | grep ai`

Expected: AI-related artisan commands are visible (e.g., `make:agent`, `make:tool`).

**Step 5: Commit**

```bash
git add -A && git commit -m "chore: install laravel/ai SDK"
```

### Task 2: Configure OpenRouter Provider

**Files:**
- Modify: `config/ai.php`

**Step 1: Configure OpenRouter in config/ai.php**

Open `config/ai.php` and add/update the OpenRouter provider configuration. The AI SDK uses Prism under the hood, so the driver name should match Prism's provider name:

```php
'providers' => [
    'openrouter' => [
        'driver' => 'openrouter',
        'key' => env('OPENROUTER_API_KEY'),
        'url' => env('OPENROUTER_URL', 'https://openrouter.ai/api/v1'),
    ],
],
```

Also set the default provider and model if there's a `default` key:

```php
'default' => [
    'provider' => 'openrouter',
    'model' => 'google/gemini-2.5-flash',
],
```

**Step 2: Verify the config works**

Run tinker to test:

```php
use function Laravel\Ai\{agent};
$response = agent(instructions: 'Reply with "hello"')->prompt('Say hello', provider: 'openrouter', model: 'google/gemini-2.5-flash');
echo $response;
```

If `provider: 'openrouter'` doesn't work as a string, try `Lab::OpenRouter` or check the `Lab` enum for the correct value. Adjust accordingly. The key thing to verify is the exact way to reference the OpenRouter provider.

**Step 3: Commit**

```bash
git add config/ai.php && git commit -m "feat: configure OpenRouter provider for AI SDK"
```

---

## Phase 2: Migrate Existing Prism Call Sites

All 6 existing Prism call sites need to be converted to AI SDK anonymous agents. Start with the simplest (no streaming, no tests) and work toward the most complex.

### Task 3: Migrate CrawlWebpageInformation

**Files:**
- Modify: `modules/Holocron/Bookmarks/Jobs/CrawlWebpageInformation.php`
- Modify: `modules/Holocron/Bookmarks/Tests/WebpagesTest.php`

**Step 1: Update the test to use AI SDK fakes**

In `modules/Holocron/Bookmarks/Tests/WebpagesTest.php`, replace:

```php
use Prism\Prism\Prism;
use Prism\Prism\Testing\TextResponseFake;
```

With the AI SDK equivalent. The anonymous `agent()` function needs to be faked. Check the AI SDK docs for how to fake anonymous agents. The approach will likely be one of:

```php
// Option A: If there's a global fake
use Laravel\Ai\AnonymousAgent; // or similar
AnonymousAgent::fake(['Good day sir!!']);
```

```php
// Option B: If faking works differently for anonymous agents
// Check the AI SDK source after installation
```

Replace `Prism::fake([TextResponseFake::make()->withText('Good day sir!!')])` with the correct AI SDK fake.

**Step 2: Run the test to verify it fails**

Run: `php artisan test modules/Holocron/Bookmarks/Tests/WebpagesTest.php`

Expected: FAIL (because the job still uses Prism).

**Step 3: Update the job**

In `modules/Holocron/Bookmarks/Jobs/CrawlWebpageInformation.php`, replace:

```php
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
```

With:

```php
use function Laravel\Ai\{agent};
```

Replace the `createSummary` method body:

```php
protected function createSummary(string $content): string
{
    $content = mb_substr($content, 0, 400_000);

    $response = agent(
        instructions: <<<'EOT'
Summarize the given webpage content in 1-2 sentences, focus on the purpose only. Exclude information like:
- login elements
- contact information
- cookie consent / data privacy
- footer information
- payment / upgrade information

If no content was provided answer with "The summary could not be generated."

You will answer ONLY with the summary, no quotes, delimiters.
EOT,
    )->prompt($content);

    return (string) $response;
}
```

Note: The provider/model may be inherited from config defaults. If not, pass them explicitly:
`->prompt($content, provider: 'openrouter', model: 'google/gemini-2.0-flash-001')`

**Step 4: Run test to verify it passes**

Run: `php artisan test modules/Holocron/Bookmarks/Tests/WebpagesTest.php`

Expected: PASS

**Step 5: Commit**

```bash
git add modules/Holocron/Bookmarks/ && git commit -m "refactor: migrate CrawlWebpageInformation from Prism to AI SDK"
```

### Task 4: Migrate Articles/Show.php

**Files:**
- Modify: `app/Livewire/Articles/Show.php`

No existing tests for this feature.

**Step 1: Update imports and the rambleIt method**

Replace Prism imports:

```php
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
```

With:

```php
use function Laravel\Ai\{agent};
```

Replace the `rambleIt` method body:

```php
private function rambleIt(string $original): string
{
    return cache()->store('file_persistent')->rememberForever('ramble.'.$this->article->id, function () use ($original) {
        $response = agent(
            instructions: <<<'EOT'
- only return markdown
- don't include any code highlighting backticks
- keep the heading structure of the article
- make sure that frontmatter is valid, values must be enclosed in double quotes if they are of type string
EOT,
        )->prompt(
            <<<EOT
Rewrite the following blog post from the perspective of a frustrated, 35-year-old who's fed up with the cluelessness around them. This person's got no patience left for people who just can't get a handle on the simplest things, keep burning up everyone's time, and never make a single change to improve their sloppy workflows. Instead, they endlessly complain about how overwhelmed they are. As they type, they get progressively angrier, using more intense language, slurs, and visceral imagery—basically writing themselves into a bottomless rage. Expect the f-word to make increasingly frequent appearances as they rant. The core message should be:  _"Stop wasting my and your fucking time."_

```
$original
```
EOT,
        );

        return (string) $response;
    });
}
```

**Step 2: Verify manually**

Visit a blog post page and click the "Ramble" toggle. Clear the cached version first if needed to test a fresh generation.

**Step 3: Commit**

```bash
git add app/Livewire/Articles/Show.php && git commit -m "refactor: migrate Articles/Show ramble from Prism to AI SDK"
```

### Task 5: Migrate WithAI.php (Structured Output)

**Files:**
- Modify: `modules/Holocron/Quest/Livewire/WithAI.php`

No existing tests for this feature.

**Step 1: Update the trait**

Replace Prism imports:

```php
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\StringSchema;
```

With:

```php
use Illuminate\Contracts\JsonSchema\JsonSchema;
use function Laravel\Ai\{agent};
```

Replace the `generateSubquests` method:

```php
public function generateSubquests(): void
{
    $children = $this->quest->children->implode('name', '\n');

    $response = agent(
        schema: fn (JsonSchema $schema) => [
            'subtasks' => $schema->array(items: $schema->string())->required(),
        ],
    )->prompt(view('prompts.subquests', [
        'name' => $this->name,
        'description' => $this->description,
        'children' => $children,
    ]));

    $this->subquestSuggestions = $response['subtasks'];
}
```

Note: The exact schema syntax may need adjustment. Check the `JsonSchema` interface after installation for the correct `array()` method signature.

**Step 2: Commit**

```bash
git add modules/Holocron/Quest/Livewire/WithAI.php && git commit -m "refactor: migrate WithAI subquest generation from Prism to AI SDK"
```

### Task 6: Migrate Notifications/Chopper.php (Discord Bot)

**Files:**
- Modify: `app/Notifications/Chopper.php`

No existing tests. This class manages multi-turn conversation via cache.

**Step 1: Update the class**

Replace Prism imports:

```php
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;
```

With:

```php
use Laravel\Ai\Messages\Message;
use function Laravel\Ai\{agent};
```

Replace the `conversation` method:

```php
public static function conversation(string $message, string $topic, ?CarbonImmutable $ttl = null): string
{
    $history = cache("chopper.$topic", []);
    $history[] = new Message('user', $message);

    $response = agent(
        instructions: self::personality(),
        messages: $history,
    )->prompt($message);

    $answer = (string) $response;

    $history[] = new Message('assistant', $answer);

    logger()->channel('chopper')->info('Chopper', ['topic' => $topic, 'history' => $history]);

    cache(["chopper.$topic" => $history], $ttl ?? now()->endOfDay());

    return $answer;
}
```

Note: Check whether `Message` is the correct class name in the AI SDK. It might be `Laravel\Ai\Messages\Message` or similar. Verify after installation.

**Step 2: Commit**

```bash
git add app/Notifications/Chopper.php && git commit -m "refactor: migrate Discord Chopper from Prism to AI SDK"
```

### Task 7: Migrate WithNotes.php (Streaming)

**Files:**
- Modify: `modules/Holocron/Quest/Livewire/WithNotes.php`
- Modify: `modules/Holocron/Quest/Tests/QuestTest.php`

**Step 1: Update the test**

In `modules/Holocron/Quest/Tests/QuestTest.php`, replace:

```php
use Prism\Prism\Prism;
```

And the `Prism::fake()` calls with the AI SDK equivalent.

For the `'can add links'` test, replace `Prism::fake()` — since this test only needs to prevent real API calls during the link-adding flow, it just needs a generic fake.

For the `'does not accumulate streamed content'` test, replace:

```php
Prism::fake([
    \Prism\Prism\Testing\TextResponseFake::make()
        ->withText('First AI response'),
    \Prism\Prism\Testing\TextResponseFake::make()
        ->withText('Second AI response'),
]);
```

With the AI SDK anonymous agent fake equivalent. Check the AI SDK docs for how to fake anonymous agents with sequential responses.

**Step 2: Run the tests to verify they fail**

Run: `php artisan test modules/Holocron/Quest/Tests/QuestTest.php`

Expected: FAIL (WithNotes still uses Prism).

**Step 3: Update the trait**

Replace Prism imports in `modules/Holocron/Quest/Livewire/WithNotes.php`:

```php
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\SystemMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;
```

With:

```php
use Laravel\Ai\Messages\Message;
use function Laravel\Ai\{agent};
```

Replace the `ask` method:

```php
public function ask(int $noteId): void
{
    $this->streamedAnswer = '';

    $messages = [];
    $prompt = <<<PROMPT
Aktuelle Aufgabe:

- Name: {$this->quest->name}
  Beschreibung: {$this->quest->description}
PROMPT;

    $prompt .= <<<'PROMPT'
Aufgabenstruktur:
---
PROMPT;

    foreach ($this->quest->breadcrumb() as $index => $quest) {
        $indent = str_repeat('  ', $index);

        $prompt .= <<<PROMPT
$indent- Name: {$quest->name}
$indent  Beschreibung: {$quest->description}
PROMPT;
    }

    $prompt .= '---';

    $messages[] = new Message('user', $prompt);

    $this->quest->notes->each(function (Note $note) use (&$messages) {
        if (is_null($note->content)) {
            return;
        }
        $messages[] = new Message($note->role, $note->content);
    });

    $stream = agent(
        instructions: view('prompts.solution')->render(),
        messages: $messages,
    )->stream($this->quest->notes->last()?->content ?? '');

    foreach ($stream as $event) {
        $content = $event->text ?? '';

        if (empty($content)) {
            continue;
        }
        $this->streamedAnswer .= $content;

        $this->stream(
            str($this->streamedAnswer)->markdown(),
            el: 'streamedAnswer',
            replace: true,
        );
    }

    Note::find($noteId)->update([
        'content' => str($this->streamedAnswer)->markdown(),
    ]);
}
```

Note: The exact stream event property (e.g., `$event->text`) may differ. Check the AI SDK's stream event structure after installation.

**Step 4: Run the tests**

Run: `php artisan test modules/Holocron/Quest/Tests/QuestTest.php`

Expected: PASS

**Step 5: Commit**

```bash
git add modules/Holocron/Quest/ && git commit -m "refactor: migrate WithNotes streaming from Prism to AI SDK"
```

---

## Phase 3: Build ChopperAgent and Tools

### Task 8: Create Read Tools

**Files:**
- Create: `app/Ai/Tools/SearchQuests.php`
- Create: `app/Ai/Tools/SearchNotes.php`
- Create: `app/Ai/Tools/ListQuests.php`
- Create: `app/Ai/Tools/GetQuest.php`

**Step 1: Generate tool scaffolds**

Run:
```bash
php artisan make:tool SearchQuests
php artisan make:tool SearchNotes
php artisan make:tool ListQuests
php artisan make:tool GetQuest
```

**Step 2: Implement SearchQuests**

In `app/Ai/Tools/SearchQuests.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Models\Quest;
use Stringable;

class SearchQuests implements Tool
{
    public function description(): Stringable|string
    {
        return 'Search quests using semantic/vector search. Returns matching quests with their name, description, completion status, and date.';
    }

    public function handle(Request $request): Stringable|string
    {
        $results = Quest::search($request['query'])->options([
            'query_by' => 'embedding',
            'prefix' => false,
            'drop_tokens_threshold' => 0,
            'per_page' => $request['limit'] ?? 5,
        ])->get()->take($request['limit'] ?? 5);

        if ($results->isEmpty()) {
            return 'No quests found matching the query.';
        }

        return $results->map(fn (Quest $quest) => sprintf(
            'ID: %d | Name: %s | Description: %s | Completed: %s | Date: %s',
            $quest->id,
            $quest->name,
            str($quest->description)->stripTags()->limit(200),
            $quest->isCompleted() ? 'Yes' : 'No',
            $quest->date?->format('Y-m-d') ?? 'none',
        ))->implode("\n");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->required(),
            'limit' => $schema->integer()->min(1)->max(10),
        ];
    }
}
```

**Step 3: Implement SearchNotes**

In `app/Ai/Tools/SearchNotes.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Models\Note;
use Stringable;

class SearchNotes implements Tool
{
    public function description(): Stringable|string
    {
        return 'Search notes/comments on quests using semantic/vector search. Returns matching notes with their content and associated quest.';
    }

    public function handle(Request $request): Stringable|string
    {
        $results = Note::search($request['query'])->options([
            'query_by' => 'embedding',
            'prefix' => false,
            'drop_tokens_threshold' => 0,
            'per_page' => $request['limit'] ?? 5,
        ])->get()->take($request['limit'] ?? 5)->load('quest');

        if ($results->isEmpty()) {
            return 'No notes found matching the query.';
        }

        return $results->map(fn (Note $note) => sprintf(
            'Note ID: %d | Quest: %s (ID: %d) | Content: %s',
            $note->id,
            $note->quest->name,
            $note->quest_id,
            str($note->content)->stripTags()->limit(300),
        ))->implode("\n");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->required(),
            'limit' => $schema->integer()->min(1)->max(10),
        ];
    }
}
```

**Step 4: Implement ListQuests**

In `app/Ai/Tools/ListQuests.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Models\Quest;
use Stringable;

class ListQuests implements Tool
{
    public function description(): Stringable|string
    {
        return 'List quests with optional filters. Can filter by: open (not completed), today (scheduled for today or earlier), daily, notes-only. Returns quest name, description, date, and completion status.';
    }

    public function handle(Request $request): Stringable|string
    {
        $query = Quest::query();

        $filter = $request['filter'] ?? 'open';

        $query = match ($filter) {
            'open' => $query->notCompleted()->areNotNotes(),
            'today' => $query->notCompleted()->today()->areNotNotes(),
            'daily' => $query->notCompleted()->where('daily', true)->areNotNotes(),
            'completed' => $query->completed()->areNotNotes(),
            'notes' => $query->areNotes(),
            'all' => $query->areNotNotes(),
            default => $query->notCompleted()->areNotNotes(),
        };

        $results = $query->latest()->limit($request['limit'] ?? 20)->get();

        if ($results->isEmpty()) {
            return "No quests found with filter '$filter'.";
        }

        return $results->map(fn (Quest $quest) => sprintf(
            'ID: %d | Name: %s | Date: %s | Completed: %s',
            $quest->id,
            $quest->name,
            $quest->date?->format('Y-m-d') ?? 'none',
            $quest->isCompleted() ? 'Yes' : 'No',
        ))->implode("\n");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'filter' => $schema->string(),
            'limit' => $schema->integer()->min(1)->max(50),
        ];
    }
}
```

**Step 5: Implement GetQuest**

In `app/Ai/Tools/GetQuest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Models\Quest;
use Stringable;

class GetQuest implements Tool
{
    public function description(): Stringable|string
    {
        return 'Get detailed information about a specific quest by ID, including its children (sub-quests) and notes/comments.';
    }

    public function handle(Request $request): Stringable|string
    {
        $quest = Quest::with(['children', 'notes'])->find($request['quest_id']);

        if (! $quest) {
            return "Quest with ID {$request['quest_id']} not found.";
        }

        $output = sprintf(
            "Quest ID: %d\nName: %s\nDescription: %s\nDate: %s\nCompleted: %s\nIs Note: %s\nDaily: %s",
            $quest->id,
            $quest->name,
            str($quest->description)->stripTags(),
            $quest->date?->format('Y-m-d') ?? 'none',
            $quest->isCompleted() ? 'Yes' : 'No',
            $quest->is_note ? 'Yes' : 'No',
            $quest->daily ? 'Yes' : 'No',
        );

        if ($quest->children->isNotEmpty()) {
            $output .= "\n\nSub-quests:";
            foreach ($quest->children as $child) {
                $output .= sprintf(
                    "\n  - ID: %d | %s | Completed: %s",
                    $child->id,
                    $child->name,
                    $child->isCompleted() ? 'Yes' : 'No',
                );
            }
        }

        if ($quest->notes->isNotEmpty()) {
            $output .= "\n\nNotes:";
            foreach ($quest->notes as $note) {
                $output .= sprintf(
                    "\n  - [%s] %s (%s)",
                    $note->role ?? 'user',
                    str($note->content)->stripTags()->limit(200),
                    $note->created_at->format('Y-m-d H:i'),
                );
            }
        }

        return $output;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'quest_id' => $schema->integer()->required(),
        ];
    }
}
```

**Step 6: Commit**

```bash
git add app/Ai/Tools/ && git commit -m "feat: add read tools for ChopperAgent (SearchQuests, SearchNotes, ListQuests, GetQuest)"
```

### Task 9: Create Write Tools

**Files:**
- Create: `app/Ai/Tools/CreateQuest.php`
- Create: `app/Ai/Tools/CompleteQuest.php`
- Create: `app/Ai/Tools/AddNoteToQuest.php`

**Step 1: Generate tool scaffolds**

Run:
```bash
php artisan make:tool CreateQuest
php artisan make:tool CompleteQuest
php artisan make:tool AddNoteToQuest
```

**Step 2: Implement CreateQuest**

In `app/Ai/Tools/CreateQuest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Models\Quest;
use Stringable;

class CreateQuest implements Tool
{
    public function description(): Stringable|string
    {
        return 'Create a new quest (task) or note. Provide a name and optional description, date, parent quest ID, and whether it is a note instead of a task.';
    }

    public function handle(Request $request): Stringable|string
    {
        $quest = Quest::create([
            'name' => $request['name'],
            'description' => $request['description'] ?? '',
            'date' => $request['date'] ?? null,
            'quest_id' => $request['parent_id'] ?? null,
            'is_note' => $request['is_note'] ?? false,
            'attachments' => [],
        ]);

        return sprintf('Quest created successfully. ID: %d, Name: %s', $quest->id, $quest->name);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->required(),
            'description' => $schema->string(),
            'date' => $schema->string(),
            'parent_id' => $schema->integer(),
            'is_note' => $schema->boolean(),
        ];
    }
}
```

**Step 3: Implement CompleteQuest**

In `app/Ai/Tools/CompleteQuest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Models\Quest;
use Stringable;

class CompleteQuest implements Tool
{
    public function description(): Stringable|string
    {
        return 'Mark a quest as completed by its ID. This awards XP to the user.';
    }

    public function handle(Request $request): Stringable|string
    {
        $quest = Quest::find($request['quest_id']);

        if (! $quest) {
            return "Quest with ID {$request['quest_id']} not found.";
        }

        if ($quest->isCompleted()) {
            return "Quest '{$quest->name}' is already completed.";
        }

        $quest->complete();

        return sprintf("Quest '%s' (ID: %d) has been marked as completed.", $quest->name, $quest->id);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'quest_id' => $schema->integer()->required(),
        ];
    }
}
```

**Step 4: Implement AddNoteToQuest**

In `app/Ai/Tools/AddNoteToQuest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Actions\CreateNote;
use Modules\Holocron\Quest\Models\Quest;
use Stringable;

class AddNoteToQuest implements Tool
{
    public function description(): Stringable|string
    {
        return 'Add a note/comment to an existing quest. Useful for adding context, updates, or information to a task.';
    }

    public function handle(Request $request): Stringable|string
    {
        $quest = Quest::find($request['quest_id']);

        if (! $quest) {
            return "Quest with ID {$request['quest_id']} not found.";
        }

        $note = (new CreateNote)->handle($quest, [
            'content' => $request['content'],
        ]);

        return sprintf("Note added to quest '%s' (ID: %d). Note ID: %d", $quest->name, $quest->id, $note->id);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'quest_id' => $schema->integer()->required(),
            'content' => $schema->string()->required(),
        ];
    }
}
```

**Step 5: Commit**

```bash
git add app/Ai/Tools/ && git commit -m "feat: add write tools for ChopperAgent (CreateQuest, CompleteQuest, AddNoteToQuest)"
```

### Task 10: Create ChopperAgent

**Files:**
- Create: `app/Ai/Agents/ChopperAgent.php`

**Step 1: Generate the agent scaffold**

Run: `php artisan make:agent ChopperAgent`

**Step 2: Implement ChopperAgent**

In `app/Ai/Agents/ChopperAgent.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Tools\AddNoteToQuest;
use App\Ai\Tools\CompleteQuest;
use App\Ai\Tools\CreateQuest;
use App\Ai\Tools\GetQuest;
use App\Ai\Tools\ListQuests;
use App\Ai\Tools\SearchNotes;
use App\Ai\Tools\SearchQuests;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

#[Model('google/gemini-2.5-flash')]
#[MaxSteps(10)]
class ChopperAgent implements Agent, Conversational, HasTools
{
    use Promptable;
    use RemembersConversations;

    public function instructions(): Stringable|string
    {
        $date = now()->format('l, j. F Y');
        $time = now()->toTimeString();

        return <<<EOT
Du bist Chopper, ein hilfreicher Assistent basierend auf dem Droiden C1-10P aus Star Wars Rebels.
Heute ist $date, es ist $time Uhr.

Du bist in ein Aufgaben- und Notizverwaltungssystem integriert. Du kannst Aufgaben (Quests) suchen, auflisten, erstellen und abschließen. Du kannst auch Notizen zu Aufgaben hinzufügen und durchsuchen.

Regeln:
- Antworte immer auf Deutsch, es sei denn, der Benutzer schreibt auf Englisch.
- Sei humorvoll und motivierend, aber bleibe hilfreich und präzise.
- Verwende deine Tools aktiv, um dem Benutzer bestmöglich zu helfen.
- Wenn du nach Aufgaben gefragt wirst, nutze die Such- und Listenwerkzeuge.
- Formatiere deine Antworten mit Markdown.
- Halte deine Antworten kurz und fokussiert.
EOT;
    }

    public function tools(): iterable
    {
        return [
            new SearchQuests,
            new SearchNotes,
            new ListQuests,
            new GetQuest,
            new CreateQuest,
            new CompleteQuest,
            new AddNoteToQuest,
        ];
    }
}
```

Note: The `#[Provider(...)]` attribute may need to be added depending on whether the default provider from config is used. If OpenRouter isn't the default, add:
```php
use Laravel\Ai\Attributes\Provider;
#[Provider('openrouter')]
```
Adjust based on how the `Provider` attribute accepts values (Lab enum vs string).

**Step 3: Commit**

```bash
git add app/Ai/Agents/ && git commit -m "feat: create ChopperAgent with tools and conversation persistence"
```

---

## Phase 4: Build Chat UI

### Task 11: Rewrite Chopper Livewire Component

**Files:**
- Modify: `modules/Holocron/_Shared/Livewire/Chopper.php`

**Step 1: Rewrite the component**

Replace the entire contents of `modules/Holocron/_Shared/Livewire/Chopper.php`:

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Livewire;

use App\Ai\Agents\ChopperAgent;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Title;

#[Title('Chopper')]
class Chopper extends HolocronComponent
{
    public string $message = '';

    public ?string $conversationId = null;

    public string $streamedResponse = '';

    /** @var array<int, array{role: string, content: string}> */
    public array $messages = [];

    public function mount(?string $conversation = null): void
    {
        if ($conversation) {
            $this->conversationId = $conversation;
            $this->loadMessages();
        }
    }

    public function send(): void
    {
        if (empty(trim($this->message))) {
            return;
        }

        $userMessage = $this->message;
        $this->message = '';
        $this->streamedResponse = '';

        $this->messages[] = ['role' => 'user', 'content' => $userMessage];

        $agent = new ChopperAgent;

        if ($this->conversationId) {
            $stream = $agent->continue($this->conversationId, as: auth()->user())
                ->stream($userMessage);
        } else {
            $stream = $agent->forUser(auth()->user())
                ->stream($userMessage);
        }

        foreach ($stream as $event) {
            $content = $event->text ?? '';

            if (empty($content)) {
                continue;
            }

            $this->streamedResponse .= $content;

            $this->stream(
                str($this->streamedResponse)->markdown(),
                el: 'assistant-response',
                replace: true,
            );
        }

        $this->messages[] = ['role' => 'assistant', 'content' => $this->streamedResponse];

        // Capture conversation ID for new conversations
        if (! $this->conversationId) {
            // The conversation ID should be available from the stream/response
            // Check the exact API: $stream->conversationId, $stream->response->conversationId, etc.
            $this->conversationId = $stream->conversationId ?? null;
        }

        $this->streamedResponse = '';
    }

    public function selectConversation(string $id): void
    {
        $this->conversationId = $id;
        $this->messages = [];
        $this->loadMessages();
    }

    public function newConversation(): void
    {
        $this->conversationId = null;
        $this->messages = [];
        $this->streamedResponse = '';
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function getConversations(): \Illuminate\Support\Collection
    {
        return DB::table('ai_conversations')
            ->where('user_id', auth()->id())
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();
    }

    public function render(): View
    {
        return view('holocron::chopper', [
            'conversations' => $this->getConversations(),
        ]);
    }

    protected function loadMessages(): void
    {
        if (! $this->conversationId) {
            return;
        }

        // Load messages from the AI conversations table
        // The exact table/column structure depends on the AI SDK migration
        // Check the migration file for column names (likely 'messages' as JSON)
        $conversation = DB::table('ai_conversations')
            ->where('id', $this->conversationId)
            ->first();

        if ($conversation) {
            $stored = json_decode($conversation->messages ?? '[]', true);
            $this->messages = collect($stored)->map(fn (array $msg) => [
                'role' => $msg['role'],
                'content' => $msg['content'] ?? '',
            ])->filter(fn (array $msg) => in_array($msg['role'], ['user', 'assistant']))
                ->values()
                ->all();
        }
    }
}
```

**Important notes:**
- The `getConversations()`, `loadMessages()`, and conversation ID capture methods depend on the exact DB schema created by the AI SDK migration. After running the migration in Task 1, check the table structure with `php artisan db:show --table=ai_conversations` (or equivalent) and adjust column names.
- The `$stream->conversationId` access pattern needs verification. Check the `StreamedAgentResponse` class or the stream object for how to get the conversation ID after streaming.
- The `$event->text` property name needs verification against the actual stream event class.

**Step 2: Commit**

```bash
git add modules/Holocron/_Shared/Livewire/Chopper.php && git commit -m "feat: rewrite Chopper as multi-turn chat with conversation persistence"
```

### Task 12: Rewrite Chopper Blade View

**Files:**
- Modify: `modules/Holocron/_Shared/Views/chopper.blade.php`

**Step 1: Rewrite the view**

Replace the entire contents of `modules/Holocron/_Shared/Views/chopper.blade.php`:

```blade
<div class="flex h-[calc(100vh-12rem)] gap-6">
    {{-- Sidebar: Conversation List --}}
    <div class="hidden w-64 shrink-0 flex-col gap-2 overflow-y-auto md:flex">
        <flux:button wire:click="newConversation" variant="primary" class="w-full">
            Neues Gespräch
        </flux:button>

        <div class="mt-2 flex flex-col gap-1">
            @foreach ($conversations as $conv)
                <button
                    wire:click="selectConversation('{{ $conv->id }}')"
                    wire:key="conv-{{ $conv->id }}"
                    @class([
                        'w-full truncate rounded-lg px-3 py-2 text-left text-sm transition',
                        'bg-zinc-100 dark:bg-zinc-800' => $conversationId === $conv->id,
                        'hover:bg-zinc-50 dark:hover:bg-zinc-900' => $conversationId !== $conv->id,
                    ])
                >
                    {{ str($conv->title ?? $conv->updated_at)->limit(40) }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Mobile: Conversation Dropdown --}}
    <div class="mb-4 md:hidden">
        <flux:dropdown>
            <flux:button variant="ghost" icon="chat-bubble-left-right">
                Gespräche
            </flux:button>
            <flux:menu>
                <flux:menu.item wire:click="newConversation" icon="plus">
                    Neues Gespräch
                </flux:menu.item>
                <flux:separator />
                @foreach ($conversations as $conv)
                    <flux:menu.item
                        wire:click="selectConversation('{{ $conv->id }}')"
                        wire:key="conv-mobile-{{ $conv->id }}"
                    >
                        {{ str($conv->title ?? $conv->updated_at)->limit(40) }}
                    </flux:menu.item>
                @endforeach
            </flux:menu>
        </flux:dropdown>
    </div>

    {{-- Main Chat Area --}}
    <div class="flex min-w-0 flex-1 flex-col">
        {{-- Messages --}}
        <div
            class="flex-1 space-y-4 overflow-y-auto pb-4"
            id="chat-messages"
            x-data
            x-effect="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
        >
            @if (empty($messages) && !$streamedResponse)
                <div class="flex h-full items-center justify-center text-zinc-400">
                    <div class="text-center">
                        <flux:icon.sparkles class="mx-auto size-12" />
                        <p class="mt-2">Stelle Chopper eine Frage</p>
                    </div>
                </div>
            @endif

            @foreach ($messages as $index => $msg)
                <div
                    wire:key="msg-{{ $index }}"
                    @class([
                        'flex',
                        'justify-end' => $msg['role'] === 'user',
                        'justify-start' => $msg['role'] === 'assistant',
                    ])
                >
                    <div
                        @class([
                            'max-w-[80%] rounded-2xl px-4 py-2',
                            'bg-zinc-800 text-white dark:bg-zinc-200 dark:text-zinc-900' => $msg['role'] === 'user',
                            'bg-zinc-100 dark:bg-zinc-800' => $msg['role'] === 'assistant',
                        ])
                    >
                        @if ($msg['role'] === 'assistant')
                            <div class="prose dark:prose-invert prose-sm">
                                {!! str($msg['content'])->markdown() !!}
                            </div>
                        @else
                            {{ $msg['content'] }}
                        @endif
                    </div>
                </div>
            @endforeach

            {{-- Streaming response --}}
            @if ($streamedResponse)
                <div class="flex justify-start">
                    <div class="max-w-[80%] rounded-2xl bg-zinc-100 px-4 py-2 dark:bg-zinc-800">
                        <div class="prose dark:prose-invert prose-sm" wire:stream="assistant-response">
                            {!! str($streamedResponse)->markdown() !!}
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Input --}}
        <form wire:submit="send" class="mt-4 flex gap-2">
            <flux:input
                wire:model="message"
                placeholder="Nachricht an Chopper..."
                autofocus
            />
            <flux:button type="submit" variant="primary" icon="paper-airplane" wire:loading.attr="disabled" />
        </form>
    </div>
</div>
```

**Notes:**
- The `$conv->title` column may not exist on the `ai_conversations` table. Check the migration schema. If there's no title column, fall back to showing the date or first message excerpt.
- The auto-scroll uses Alpine.js `x-effect` to scroll to bottom on changes.
- The `wire:stream="assistant-response"` matches the `el: 'assistant-response'` in the Livewire component.

**Step 2: Test manually**

Visit `/holocron/chopper` and verify:
1. The chat UI renders correctly
2. You can type a message and see a streaming response
3. The conversation sidebar shows past conversations
4. You can switch between conversations
5. Starting a new conversation works

**Step 3: Commit**

```bash
git add modules/Holocron/_Shared/Views/chopper.blade.php && git commit -m "feat: rewrite Chopper chat UI with conversation sidebar"
```

---

## Phase 5: Tests and Cleanup

### Task 13: Write ChopperAgent Tests

**Files:**
- Create: `modules/Holocron/_Shared/Tests/ChopperTest.php`

**Step 1: Create the test file**

Run: `php artisan make:test --pest modules/Holocron/_Shared/Tests/ChopperTest`

This won't work because the path is non-standard. Create the file manually.

**Step 2: Write tests**

In `modules/Holocron/_Shared/Tests/ChopperTest.php`:

```php
<?php

declare(strict_types=1);

use App\Ai\Agents\ChopperAgent;
use Modules\Holocron\_Shared\Livewire\Chopper;
use Modules\Holocron\Quest\Models\Quest;

use function Pest\Laravel\actingAs;

it('renders the chopper page', function () {
    actingAs(tim())
        ->get(route('holocron.chopper'))
        ->assertSuccessful()
        ->assertSeeLivewire(Chopper::class);
});

it('can send a message and receive a response', function () {
    ChopperAgent::fake(['Hallo! Wie kann ich dir helfen?']);

    Livewire::test(Chopper::class)
        ->set('message', 'Hallo Chopper!')
        ->call('send')
        ->assertSet('message', '');

    ChopperAgent::assertPrompted(fn ($prompt) => str_contains($prompt->prompt, 'Hallo Chopper!'));
});

it('can start a new conversation', function () {
    ChopperAgent::fake();

    Livewire::test(Chopper::class)
        ->set('message', 'Test')
        ->call('send')
        ->call('newConversation')
        ->assertSet('conversationId', null)
        ->assertSet('messages', []);
});
```

Note: The exact `ChopperAgent::fake()` API and assertion methods depend on the AI SDK. Check `Laravel\Ai\Contracts\Agent` and the fake/testing utilities. The `tim()` helper function should be replaced with whatever helper creates the authenticated test user in this project — check the `Pest.php` file or test helpers.

**Step 3: Run the tests**

Run: `php artisan test modules/Holocron/_Shared/Tests/ChopperTest.php`

Expected: PASS

**Step 4: Commit**

```bash
git add modules/Holocron/_Shared/Tests/ && git commit -m "test: add ChopperAgent and Chopper UI tests"
```

### Task 14: Clean Up

**Files:**
- Delete: `config/prism.php`
- Delete: `config/denk.php`
- Modify: `composer.json`

**Step 1: Remove config/prism.php and config/denk.php**

```bash
rm config/prism.php config/denk.php
```

**Step 2: Remove direct Prism dependency from composer.json**

Run: `composer remove prism-php/prism`

Prism will remain as a transitive dependency of `laravel/ai`, but won't be directly required.

**Step 3: Verify no remaining Prism imports**

Run: `grep -r "use Prism\\\\Prism" app/ modules/ --include="*.php"`

Expected: No results. If any remain, fix them.

**Step 4: Run pint**

Run: `vendor/bin/pint --dirty`

**Step 5: Run the full test suite**

Run: `php artisan test`

Expected: All tests pass.

**Step 6: Commit**

```bash
git add -A && git commit -m "chore: remove Prism config files and direct dependency"
```

---

## Verification Checklist

After all tasks are complete, verify:

- [ ] `php artisan test` — all tests pass
- [ ] `/holocron/chopper` — chat UI renders, can send messages, streaming works
- [ ] Conversations persist — can refresh page and see past conversations in sidebar
- [ ] Tools work — ask Chopper to list quests, create a quest, complete a quest
- [ ] `Articles/Show` ramble feature still works
- [ ] `WithNotes` quest chat still works
- [ ] `WithAI` subquest generation still works
- [ ] `CrawlWebpageInformation` job still works
- [ ] `vendor/bin/pint --test` — no formatting issues
- [ ] No remaining `use Prism\Prism` imports anywhere
