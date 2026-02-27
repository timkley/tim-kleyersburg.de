<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Laravel\Ai\Contracts\Providers\TextProvider;
use Laravel\Ai\Messages\UserMessage;

class SummarizeConversations implements ShouldQueue
{
    use Queueable;

    public function handle(TextProvider $provider): void
    {
        $conversations = AgentConversation::query()
            ->whereDoesntHave('messages', function ($query) {
                $query->where('created_at', '>=', now()->subMinutes(30));
            })
            ->whereHas('messages')
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

        if (empty(mb_trim($messages))) {
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
