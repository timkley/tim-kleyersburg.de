<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Livewire;

use App\Ai\Agents\ChopperAgent;
use Flux;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Ai\Files\Image as AiImage;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use stdClass;

#[Title('Chopper')]
class Chopper extends HolocronComponent
{
    use WithFileUploads;

    public string $message = '';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $attachments = [];

    public ?string $conversationId = null;

    public string $streamedResponse = '';

    public bool $isStreaming = false;

    /** @var array<int, array{role: string, content: string}> */
    public array $messages = [];

    public function mount(?string $conversationId = null): void
    {
        if ($conversationId) {
            $exists = DB::table('agent_conversations')
                ->where('id', $conversationId)
                ->where('user_id', auth()->id())
                ->exists();

            if (! $exists) {
                abort(404);
            }

            $this->conversationId = $conversationId;
            $this->loadMessages();
        }
    }

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

        // Generate channel ID for new conversations
        if (! $this->conversationId) {
            $this->conversationId = Str::uuid()->toString();
        }

        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage,
            'attachments' => $storagePaths,
        ];

        $this->dispatch('chopper-subscribe', conversationId: $this->conversationId);
        $this->dispatch('message-sent');

        $this->js('$wire.ask('.json_encode($userMessage, JSON_THROW_ON_ERROR).', '.json_encode($storagePaths, JSON_THROW_ON_ERROR).')');
    }

    public function ask(string $userMessage, array $storagePaths = []): void
    {
        $agent = new ChopperAgent;

        $attachments = array_map(
            fn (string $path) => AiImage::fromStorage($path),
            $storagePaths,
        );

        $isNewConversation = ! DB::table('agent_conversations')->where('id', $this->conversationId)->exists();

        if ($isNewConversation) {
            $agent->forUser(auth()->user());
        } else {
            $agent->continue($this->conversationId, auth()->user());
        }

        $channel = new PrivateChannel('chopper.conversation.'.$this->conversationId);
        $agent->broadcastOnQueue($userMessage, $channel, $attachments);
    }

    public function streamCompleted(): void
    {
        // If conversationId was a temp UUID, find the real one
        $exists = DB::table('agent_conversations')->where('id', $this->conversationId)->exists();
        if (! $exists) {
            $realId = DB::table('agent_conversations')
                ->where('user_id', auth()->id())
                ->orderByDesc('created_at')
                ->value('id');
            if ($realId) {
                $this->conversationId = $realId;
            }
        }

        $this->loadMessages();
        $this->isStreaming = false;
        $this->streamedResponse = '';
        $this->js("history.replaceState({}, '', '".route('holocron.chopper', $this->conversationId)."')");
    }

    public function handleStreamError(string $message): void
    {
        $this->isStreaming = false;
        $this->streamedResponse = '';
        Flux::toast(text: $message, variant: 'danger');
    }

    public function removeAttachment(int $index): void
    {
        $attachments = $this->attachments;
        array_splice($attachments, $index, 1);
        $this->attachments = array_values($attachments);
    }

    public function render(): View
    {
        return view('holocron::chopper', [
            'conversations' => $this->getConversations(),
        ]);
    }

    /**
     * @return Collection<int, stdClass>
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
            return; // @codeCoverageIgnore
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
}
