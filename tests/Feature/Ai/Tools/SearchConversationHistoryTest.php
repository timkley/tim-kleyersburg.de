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
