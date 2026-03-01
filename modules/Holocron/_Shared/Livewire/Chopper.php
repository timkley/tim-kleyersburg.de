<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Livewire;

use App\Ai\Agents\ChopperAgent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Laravel\Ai\Files\Image as AiImage;
use Laravel\Ai\Responses\StreamedAgentResponse;
use Laravel\Ai\Streaming\Events\TextDelta;
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

        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage,
            'attachments' => $storagePaths,
        ];

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
                    "history.replaceState({}, '', '".route('holocron.chopper', $response->conversationId)."')"
                );
            }
        });

        $this->isStreaming = false;
        $this->streamedResponse = '';
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
