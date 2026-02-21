<div class="flex h-[calc(100vh-12rem)] gap-6">
    {{-- Sidebar: Conversation List (Desktop) --}}
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
            @if (empty($messages) && ! $streamedResponse)
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
                            <div class="prose prose-sm dark:prose-invert">
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
                        <div class="prose prose-sm dark:prose-invert" wire:stream="assistant-response">
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
