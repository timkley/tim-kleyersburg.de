<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchConversationHistory implements Tool
{
    private const int MAX_TOKENS = 25_000;

    private const int MAX_MESSAGE_TOKENS = 5_000;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Primary intent: search past conversations for relevant context. Use proactively whenever past conversation context could enrich your response — when topics might have been discussed before, when the user references something from the past, or when continuity with previous interactions would help. Do not hesitate to use this tool; if there is even a chance past context is relevant, search for it.';
    }

    /**
     * Execute the tool.
     */
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

            /** @var AgentConversation $conversation */
            $conversation = $message->conversation;

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

            $content = $message->content ?? '';
            $contentTokens = (int) ceil(mb_strlen($content) / 4);

            if ($contentTokens > self::MAX_MESSAGE_TOKENS) {
                $content = mb_substr($content, 0, self::MAX_MESSAGE_TOKENS * 4).'...';
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

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->required(),
            'limit' => $schema->integer()->min(1)->max(10),
        ];
    }
}
