# Conversation Memory Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Give Chopper proactive recall of past conversations via Typesense semantic search with token-budgeted results.

**Architecture:** Index individual user/assistant messages in Typesense with embeddings. Pre-generate conversation summaries via a scheduled job. A single `SearchConversationHistory` tool returns matched messages + deduplicated summaries, capped at ~25k tokens.

**Tech Stack:** Laravel AI (OpenRouter), Laravel Scout (Typesense), OpenAI embeddings (`text-embedding-3-small`)

---

### Task 1: Migration — add summary columns to agent_conversations

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_add_summary_to_agent_conversations_table.php`

**Step 1: Create migration**

Run: `php artisan make:migration add_summary_to_agent_conversations_table --no-interaction`

**Step 2: Write migration code**

```php
public function up(): void
{
    Schema::table('agent_conversations', function (Blueprint $table) {
        $table->text('summary')->nullable()->after('title');
        $table->timestamp('summary_generated_at')->nullable()->after('summary');
    });
}

public function down(): void
{
    Schema::table('agent_conversations', function (Blueprint $table) {
        $table->dropColumn(['summary', 'summary_generated_at']);
    });
}
```

**Step 3: Run migration**

Run: `php artisan migrate --no-interaction`
Expected: Migration runs successfully.

**Step 4: Commit**

```
feat: add summary columns to agent_conversations table
```

---

### Task 2: AgentConversation Eloquent model

**Files:**
- Create: `app/Models/AgentConversation.php`

**Step 1: Create model**

Run: `php artisan make:class App/Models/AgentConversation --no-interaction`

**Step 2: Implement model**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentConversation extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'summary_generated_at' => 'datetime',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AgentConversationMessage::class, 'conversation_id');
    }
}
```

Key details:
- Uses string primary key (`$incrementing = false`, `$keyType = 'string'`) because the `id` column is `varchar(36)` (UUID).
- Cast `summary_generated_at` to datetime.
- Has `messages()` relationship.

**Step 3: Commit**

```
feat: add AgentConversation Eloquent model
```

---

### Task 3: AgentConversationMessage Eloquent model with Scout Searchable

**Files:**
- Create: `app/Models/AgentConversationMessage.php`

**Step 1: Create model**

Run: `php artisan make:class App/Models/AgentConversationMessage --no-interaction`

**Step 2: Implement model**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class AgentConversationMessage extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    use Searchable;

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AgentConversation::class, 'conversation_id');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'role' => $this->role,
            'content' => $this->content ?? '',
            'created_at' => $this->created_at->timestamp,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return in_array($this->role, ['user', 'assistant']);
    }
}
```

Key details:
- String primary key (UUID).
- `shouldBeSearchable()` filters to only `user` and `assistant` roles — tool calls/results are excluded.
- `toSearchableArray()` follows the existing Quest/Note pattern.

**Step 3: Commit**

```
feat: add AgentConversationMessage model with Scout searchable
```

---

### Task 4: Typesense collection schema in scout.php

**Files:**
- Modify: `config/scout.php`

**Step 1: Add the collection schema**

Add `AgentConversationMessage::class` to the `model-settings` array in `config/scout.php`, after the existing `Bookmark::class` entry. Add the `use` import at the top of the file.

```php
// Add to use statements at top:
use App\Models\AgentConversationMessage;

// Add to 'model-settings' array:
AgentConversationMessage::class => [
    'collection-schema' => [
        'fields' => [
            [
                'name' => 'id',
                'type' => 'string',
            ],
            [
                'name' => 'conversation_id',
                'type' => 'string',
            ],
            [
                'name' => 'role',
                'type' => 'string',
            ],
            [
                'name' => 'content',
                'type' => 'string',
            ],
            [
                'name' => 'created_at',
                'type' => 'int64',
            ],
            [
                'name' => 'embedding',
                'type' => 'float[]',
                'embed' => [
                    'from' => [
                        'content',
                    ],
                    'model_config' => [
                        'model_name' => 'openai/text-embedding-3-small',
                        'api_key' => env('OPENAI_API_KEY'),
                    ],
                ],
            ],
        ],
        'default_sorting_field' => 'created_at',
    ],
    'search-parameters' => [
        'query_by' => 'content',
        'exclude_fields' => 'embedding',
    ],
],
```

**Step 2: Import existing messages into Typesense**

Run: `php artisan scout:import "App\Models\AgentConversationMessage"`
Expected: Messages with role `user` or `assistant` are indexed.

**Step 3: Commit**

```
feat: add Typesense collection schema for conversation messages
```

---

### Task 5: SearchConversationHistory tool — tests

**Files:**
- Create: `tests/Feature/Ai/Tools/SearchConversationHistoryTest.php`

**Step 1: Create test file**

Run: `php artisan make:test --pest Ai/Tools/SearchConversationHistoryTest --no-interaction`

**Step 2: Write tests**

Follow the pattern from `tests/Feature/Ai/Tools/SearchQuestCommentsTest.php`. Use `config(['scout.driver' => 'collection'])` to use the collection driver for testing.

Tests to write:

1. **Returns no-results message when no messages match**
   - Create a `SearchConversationHistory` tool, call `handle()` with a query that matches nothing.
   - Expect: `'No past conversations found matching the query.'`

2. **Returns matched messages with conversation summary**
   - Set scout driver to `collection`.
   - Create an `AgentConversation` with a summary.
   - Create `AgentConversationMessage` records (user + assistant) for that conversation.
   - Search for a term that matches a message.
   - Expect the result to contain: the matched message content, the conversation summary, the role, the conversation date.

3. **Deduplicates summaries for multiple matches in same conversation**
   - Create one conversation with multiple matching messages.
   - Expect the summary appears only once in the output.

4. **Excludes tool role messages from results**
   - Create messages with role `tool` — these should not be indexed or returned.

5. **Truncates messages exceeding 5k tokens (~20k chars)**
   - Create a message with very long content (>20k chars).
   - Expect the output to be truncated with `...`.

**Step 3: Run tests to verify they fail**

Run: `php artisan test --compact --filter=SearchConversationHistory`
Expected: Tests fail because `SearchConversationHistory` class doesn't exist yet.

**Step 4: Commit**

```
test: add SearchConversationHistory tool tests
```

---

### Task 6: SearchConversationHistory tool — implementation

**Files:**
- Create: `app/Ai/Tools/SearchConversationHistory.php`

**Step 1: Implement the tool**

Follow the `SearchQuests` pattern. Key implementation details:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\AgentConversationMessage;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchConversationHistory implements Tool
{
    private const int MAX_TOKENS = 25_000;

    private const int MAX_MESSAGE_TOKENS = 5_000;

    public function description(): Stringable|string
    {
        return 'Primary intent: search past conversations for relevant context. Use proactively whenever past conversation context could enrich your response — when topics might have been discussed before, when the user references something from the past, or when continuity with previous interactions would help. Do not hesitate to use this tool; if there is even a chance past context is relevant, search for it.';
    }

    public function handle(Request $request): Stringable|string
    {
        $limit = $request['limit'] ?? 10;

        $results = AgentConversationMessage::search($request['query'])->options([
            'query_by' => 'embedding',
            'prefix' => false,
            'drop_tokens_threshold' => 0,
            'per_page' => $limit,
        ])->get()->take($limit)->load('conversation');

        if ($results->isEmpty()) {
            return 'No past conversations found matching the query.';
        }

        $tokenCount = 0;
        $seenConversations = [];
        $grouped = [];

        foreach ($results as $message) {
            $conversationId = $message->conversation_id;
            $conversation = $message->conversation;

            // Build conversation header + summary (only once per conversation)
            if (! isset($seenConversations[$conversationId])) {
                $header = sprintf(
                    "--- Conversation %s (%s) ---\nSummary: %s\n",
                    $conversationId,
                    $conversation->created_at->format('Y-m-d'),
                    $conversation->summary ?? 'No summary available.',
                );
                $headerTokens = (int) ceil(mb_strlen($header) / 4);

                if ($tokenCount + $headerTokens > self::MAX_TOKENS) {
                    break;
                }

                $tokenCount += $headerTokens;
                $seenConversations[$conversationId] = true;
                $grouped[$conversationId] = $header;
            }

            // Build message line
            $content = $message->content ?? '';
            $contentTokens = (int) ceil(mb_strlen($content) / 4);

            if ($contentTokens > self::MAX_MESSAGE_TOKENS) {
                $content = mb_substr($content, 0, self::MAX_MESSAGE_TOKENS * 4) . '...';
                $contentTokens = self::MAX_MESSAGE_TOKENS;
            }

            $line = sprintf(
                "[%s, %s]: %s\n",
                ucfirst($message->role),
                $message->created_at->format('H:i'),
                $content,
            );
            $lineTokens = (int) ceil(mb_strlen($line) / 4);

            if ($tokenCount + $lineTokens > self::MAX_TOKENS) {
                break;
            }

            $tokenCount += $lineTokens;
            $grouped[$conversationId] .= $line;
        }

        return implode("\n", $grouped);
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

**Step 2: Run tests to verify they pass**

Run: `php artisan test --compact --filter=SearchConversationHistory`
Expected: All tests pass.

**Step 3: Commit**

```
feat: add SearchConversationHistory tool
```

---

### Task 7: Register tool in ChopperAgent

**Files:**
- Modify: `app/Ai/Agents/ChopperAgent.php`

**Step 1: Add the tool to the tools array**

Add `use App\Ai\Tools\SearchConversationHistory;` to imports.
Add `new SearchConversationHistory,` to the `tools()` method return array.

**Step 2: Update agent instructions**

Add to the instructions string, in the rules section:

```
- Nutze SearchConversationHistory proaktiv, wann immer vergangener Kontext deine Antwort bereichern koennte. Wenn ein Thema moeglicherweise schon besprochen wurde, wenn der Benutzer auf etwas Vergangenes verweist, oder wenn Kontinuitaet mit frueheren Gespraechen helfen wuerde — suche danach. Warte nicht auf ein explizites "erinnerst du dich" — wenn es auch nur eine Chance gibt, dass vergangener Kontext relevant ist, nutze das Tool.
```

**Step 3: Run existing agent tests**

Run: `php artisan test --compact --filter=Chopper`
Expected: All tests pass.

**Step 4: Commit**

```
feat: register SearchConversationHistory in ChopperAgent
```

---

### Task 8: SummarizeConversations job — tests

**Files:**
- Create: `tests/Feature/Ai/SummarizeConversationsTest.php`

**Step 1: Create test file**

Run: `php artisan make:test --pest Ai/SummarizeConversationsTest --no-interaction`

**Step 2: Write tests**

Tests to write:

1. **Summarizes conversations with no existing summary where last message is older than 30 minutes**
   - Create a conversation with messages older than 30 minutes, no summary.
   - Fake the AI text provider (check how Laravel AI faking works — look at existing tests or vendor source).
   - Dispatch the job.
   - Expect: `summary` is filled, `summary_generated_at` is set.

2. **Skips conversations where last message is less than 30 minutes old**
   - Create a conversation with a recent message.
   - Dispatch the job.
   - Expect: `summary` remains null.

3. **Re-summarizes conversations with messages newer than summary_generated_at**
   - Create a conversation with a summary generated 2 hours ago, but a new message from 1 hour ago.
   - Dispatch the job.
   - Expect: `summary` is updated, `summary_generated_at` is updated.

4. **Skips conversations where summary is up-to-date**
   - Create a conversation with a summary generated 1 hour ago, last message from 2 hours ago.
   - Dispatch the job.
   - Expect: `summary` unchanged.

5. **Only sends user and assistant messages to LLM**
   - Create a conversation with user, assistant, and tool messages.
   - Dispatch the job.
   - Verify the prompt sent to the LLM only contains user/assistant content.

**Step 3: Run tests to verify they fail**

Run: `php artisan test --compact --filter=SummarizeConversations`
Expected: Tests fail because the job doesn't exist yet.

**Step 4: Commit**

```
test: add SummarizeConversations job tests
```

---

### Task 9: SummarizeConversations job — implementation

**Files:**
- Create: `app/Jobs/SummarizeConversations.php`

**Step 1: Create job**

Run: `php artisan make:job SummarizeConversations --no-interaction`

**Step 2: Implement the job**

The job needs to:
1. Query conversations that need summarization using the heuristic from the design.
2. For each conversation, get user + assistant messages.
3. Send them to the LLM for summarization.
4. Update the conversation record.

Use Laravel AI's text generation pattern from `RememberConversation` middleware — specifically `$provider->textGateway()->generateText()`. Use the `cheapestTextModel()`.

```php
<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Laravel\Ai\Contracts\Providers\TextProvider;
use Laravel\Ai\Messages\UserMessage;

class SummarizeConversations implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(TextProvider $provider): void
    {
        $conversations = AgentConversation::query()
            ->whereHas('messages', function ($query) {
                $query->where('created_at', '<', now()->subMinutes(30))
                    ->latest('created_at');
            })
            ->where(function ($query) {
                $query->whereNull('summary_generated_at')
                    ->orWhereHas('messages', function ($subQuery) {
                        $subQuery->whereColumn('agent_conversation_messages.created_at', '>', 'agent_conversations.summary_generated_at');
                    });
            })
            ->get();

        foreach ($conversations as $conversation) {
            $this->summarize($conversation, $provider);
        }
    }

    private function summarize(AgentConversation $conversation, TextProvider $provider): void
    {
        $messages = AgentConversationMessage::query()
            ->where('conversation_id', $conversation->id)
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('created_at')
            ->pluck('content')
            ->implode("\n\n---\n\n");

        if (empty(trim($messages))) {
            return;
        }

        $response = $provider->textGateway()->generateText(
            $provider,
            $provider->cheapestTextModel(),
            'Summarize this conversation in 2-3 sentences. Focus on topics discussed, decisions made, and key information exchanged. Write the summary in the same language as the conversation. Respond with only the summary.',
            [new UserMessage($messages)],
        );

        $conversation->update([
            'summary' => $response->text,
            'summary_generated_at' => now(),
        ]);
    }
}
```

Key details:
- Injects `TextProvider` via dependency injection (Laravel AI resolves the default provider).
- Uses `cheapestTextModel()` for cost efficiency.
- The "idle" heuristic: `whereHas('messages')` checks last message > 30 min old, then filters for missing/outdated summary.
- Only passes user + assistant content to the LLM.

**Step 3: Run tests to verify they pass**

Run: `php artisan test --compact --filter=SummarizeConversations`
Expected: All tests pass.

**Step 4: Commit**

```
feat: add SummarizeConversations job
```

---

### Task 10: Schedule the job

**Files:**
- Modify: `bootstrap/app.php`

**Step 1: Add the job to the schedule**

Add `use App\Jobs\SummarizeConversations;` to imports.
Add to the `withSchedule` closure:

```php
$schedule->job(SummarizeConversations::class)->everyFifteenMinutes();
```

**Step 2: Commit**

```
feat: schedule SummarizeConversations job every 15 minutes
```

---

### Task 11: Run verification

**Step 1: Run full test suite**

Run: `php artisan test --compact`
Expected: All tests pass.

**Step 2: Run static analysis**

Run: `composer phpstan`
Expected: No errors.

**Step 3: Run code style**

Run: `vendor/bin/pint --dirty --format agent`
Expected: No issues, or issues auto-fixed.

**Step 4: Commit any fixes**

If Pint fixed anything, commit:
```
style: fix code formatting
```
