<?php

declare(strict_types=1);

use App\Ai\Tools\SearchConversationHistory;
use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use Illuminate\Support\Str;
use Laravel\Ai\Tools\Request;

it('returns no-results message when no messages match', function () {
    $tool = new SearchConversationHistory;
    $result = $tool->handle(new Request(['query' => 'nonexistent']));

    expect($result)->toBe('No past conversations found matching the query.');
});

it('returns matched messages with conversation summary', function () {
    config(['scout.driver' => 'collection']);

    $conversation = AgentConversation::create([
        'id' => Str::uuid()->toString(),
        'title' => 'Test Conversation',
        'summary' => 'A discussion about deployment strategies',
    ]);

    AgentConversationMessage::create([
        'id' => Str::uuid()->toString(),
        'conversation_id' => $conversation->id,
        'agent' => 'chopper',
        'role' => 'user',
        'content' => 'How should we handle blue-green deployments?',
        'attachments' => '[]',
        'tool_calls' => '[]',
        'tool_results' => '[]',
        'usage' => '{}',
        'meta' => '{}',
    ]);

    AgentConversationMessage::create([
        'id' => Str::uuid()->toString(),
        'conversation_id' => $conversation->id,
        'agent' => 'chopper',
        'role' => 'assistant',
        'content' => 'Blue-green deployments involve running two identical environments.',
        'attachments' => '[]',
        'tool_calls' => '[]',
        'tool_results' => '[]',
        'usage' => '{}',
        'meta' => '{}',
    ]);

    $tool = new SearchConversationHistory;
    $result = $tool->handle(new Request(['query' => 'blue-green deployments']));

    expect($result)
        ->toContain('A discussion about deployment strategies')
        ->toContain('blue-green deployments')
        ->toContain('User');
});

it('deduplicates summaries for multiple matches in same conversation', function () {
    config(['scout.driver' => 'collection']);

    $conversation = AgentConversation::create([
        'id' => Str::uuid()->toString(),
        'title' => 'Redis Discussion',
        'summary' => 'Talked about Redis caching patterns',
    ]);

    AgentConversationMessage::create([
        'id' => Str::uuid()->toString(),
        'conversation_id' => $conversation->id,
        'agent' => 'chopper',
        'role' => 'user',
        'content' => 'What Redis caching strategy should we use?',
        'attachments' => '[]',
        'tool_calls' => '[]',
        'tool_results' => '[]',
        'usage' => '{}',
        'meta' => '{}',
    ]);

    AgentConversationMessage::create([
        'id' => Str::uuid()->toString(),
        'conversation_id' => $conversation->id,
        'agent' => 'chopper',
        'role' => 'assistant',
        'content' => 'Redis supports several caching patterns like cache-aside.',
        'attachments' => '[]',
        'tool_calls' => '[]',
        'tool_results' => '[]',
        'usage' => '{}',
        'meta' => '{}',
    ]);

    $tool = new SearchConversationHistory;
    $result = $tool->handle(new Request(['query' => 'Redis']));

    expect(mb_substr_count($result, 'Talked about Redis caching patterns'))->toBe(1);
});

it('excludes tool role messages from results', function () {
    config(['scout.driver' => 'collection']);

    $conversation = AgentConversation::create([
        'id' => Str::uuid()->toString(),
        'title' => 'Tool Test',
        'summary' => 'Tool role test',
    ]);

    AgentConversationMessage::create([
        'id' => Str::uuid()->toString(),
        'conversation_id' => $conversation->id,
        'agent' => 'chopper',
        'role' => 'tool',
        'content' => 'secret_tool_output_should_not_appear',
        'attachments' => '[]',
        'tool_calls' => '[]',
        'tool_results' => '[]',
        'usage' => '{}',
        'meta' => '{}',
    ]);

    $tool = new SearchConversationHistory;
    $result = $tool->handle(new Request(['query' => 'secret_tool_output_should_not_appear']));

    expect($result)->toBe('No past conversations found matching the query.');
});

it('returns a description string', function () {
    $tool = new SearchConversationHistory;

    expect($tool->description())
        ->toBeString()
        ->toContain('search past conversations');
});

it('returns schema with query and limit properties', function () {
    $tool = new SearchConversationHistory;
    $schema = new Illuminate\JsonSchema\JsonSchemaTypeFactory;

    $result = $tool->schema($schema);

    expect($result)
        ->toBeArray()
        ->toHaveKeys(['query', 'limit']);
});

it('stops processing when conversation header exceeds token budget', function () {
    config(['scout.driver' => 'collection']);

    // Create many conversations each with a large summary (~2k chars = ~500 tokens per header).
    // With limit=10 results from 10 different conversations, the accumulated headers + messages
    // should exceed the MAX_TOKENS budget of 25,000 tokens, triggering the break on line 66.
    $conversations = [];
    for ($i = 0; $i < 10; $i++) {
        $conversation = AgentConversation::create([
            'id' => Str::uuid()->toString(),
            'title' => "Conversation $i",
            'summary' => str_repeat("summary_text_$i ", 1500), // ~18k chars = ~4500 tokens per header
        ]);
        $conversations[] = $conversation;

        AgentConversationMessage::create([
            'id' => Str::uuid()->toString(),
            'conversation_id' => $conversation->id,
            'agent' => 'chopper',
            'role' => 'user',
            'content' => 'headerbudget_kw message from conversation '.$i,
            'attachments' => '[]',
            'tool_calls' => '[]',
            'tool_results' => '[]',
            'usage' => '{}',
            'meta' => '{}',
        ]);
    }

    $tool = new SearchConversationHistory;
    $result = $tool->handle(new Request(['query' => 'headerbudget_kw', 'limit' => 10]));

    // Each conversation header is ~4500 tokens. The 25k token budget can fit ~5 headers.
    // Not all 10 conversations should appear because the budget runs out.
    $headerCount = mb_substr_count($result, '--- Conversation');
    expect($headerCount)->toBeGreaterThan(0)->toBeLessThan(10);
});

it('stops processing messages when line exceeds token budget', function () {
    config(['scout.driver' => 'collection']);

    $conversation = AgentConversation::create([
        'id' => Str::uuid()->toString(),
        'title' => 'Token Budget Test',
        'summary' => 'Short summary',
    ]);

    // Create messages with large content that collectively exceed the token budget.
    // MAX_TOKENS is 25_000, MAX_MESSAGE_TOKENS is 5_000.
    // Each message after truncation can be up to 5_000 tokens (~20k chars).
    // We need 6+ such messages to exceed 25k tokens.
    for ($i = 0; $i < 8; $i++) {
        AgentConversationMessage::create([
            'id' => Str::uuid()->toString(),
            'conversation_id' => $conversation->id,
            'agent' => 'chopper',
            'role' => 'user',
            'content' => 'linebudget_keyword '.str_repeat('y', 19_000), // ~4750 tokens per message
            'attachments' => '[]',
            'tool_calls' => '[]',
            'tool_results' => '[]',
            'usage' => '{}',
            'meta' => '{}',
        ]);
    }

    $tool = new SearchConversationHistory;
    $result = $tool->handle(new Request(['query' => 'linebudget_keyword']));

    // With 8 messages each ~4750 tokens, the budget of 25k tokens should cut off
    // around the 5th message. So we should see fewer than 8 "[User," entries.
    $messageLineCount = mb_substr_count($result, '[User,');
    expect($messageLineCount)->toBeLessThan(8)->toBeGreaterThan(0);
});

it('truncates messages exceeding 5k tokens', function () {
    config(['scout.driver' => 'collection']);

    $conversation = AgentConversation::create([
        'id' => Str::uuid()->toString(),
        'title' => 'Long Message',
        'summary' => 'Contains a very long message',
    ]);

    $longContent = str_repeat('deployment ', 2500);

    AgentConversationMessage::create([
        'id' => Str::uuid()->toString(),
        'conversation_id' => $conversation->id,
        'agent' => 'chopper',
        'role' => 'user',
        'content' => $longContent,
        'attachments' => '[]',
        'tool_calls' => '[]',
        'tool_results' => '[]',
        'usage' => '{}',
        'meta' => '{}',
    ]);

    $tool = new SearchConversationHistory;
    $result = $tool->handle(new Request(['query' => 'deployment']));

    expect($result)->toContain('...');
});
