<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Livewire;

use App\Ai\Agents\ChopperAgent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Laravel\Ai\Streaming\Events\TextDelta;
use Livewire\Attributes\Title;

#[Title('Chopper')]
class Chopper extends HolocronComponent
{
    public string $message = '';

    public ?string $conversationId = null;

    public string $streamedResponse = '';

    /** @var array<int, array{role: string, content: string}> */
    public array $messages = [];

    public function send(): void
    {
        if (empty(mb_trim($this->message))) {
            return;
        }

        $userMessage = $this->message;
        $this->message = '';
        $this->streamedResponse = '';

        $this->messages[] = ['role' => 'user', 'content' => $userMessage];

        $agent = new ChopperAgent;

        if ($this->conversationId) {
            $stream = $agent->continue($this->conversationId, auth()->user())->stream($userMessage);
        } else {
            $stream = $agent->forUser(auth()->user())->stream($userMessage);
        }

        foreach ($stream as $event) {
            if (! $event instanceof TextDelta) {
                continue;
            }

            $this->streamedResponse .= $event->delta;

            $this->stream(
                str($this->streamedResponse)->markdown(),
                el: 'assistant-response',
                replace: true,
            );
        }

        $this->messages[] = ['role' => 'assistant', 'content' => $this->streamedResponse];

        if (! $this->conversationId && $stream->conversationId) {
            $this->conversationId = $stream->conversationId;
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

    public function render(): View
    {
        return view('holocron::chopper', [
            'conversations' => $this->getConversations(),
        ]);
    }

    /**
     * @return Collection<int, object>
     */
    protected function getConversations(): Collection
    {
        return DB::table('agent_conversations')
            ->where('user_id', auth()->id())
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();
    }

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
            ->map(fn (object $msg) => [
                'role' => $msg->role,
                'content' => $msg->content,
            ])
            ->values()
            ->all();
    }
}
