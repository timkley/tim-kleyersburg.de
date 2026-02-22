<div class="flex flex-col md:flex-row h-[calc(100vh-12rem)] gap-2 md:gap-6">
    {{-- Sidebar: Conversation List (Desktop) --}}
    <div class="hidden w-64 shrink-0 flex-col gap-2 overflow-y-auto md:flex">
        <flux:button :href="route('holocron.chopper')" variant="primary" class="w-full" wire:navigate>
            Neues Gespräch
        </flux:button>

        <div class="mt-2 flex flex-col gap-1">
            @foreach ($conversations as $conv)
                <a
                    href="{{ route('holocron.chopper', $conv->id) }}"
                    wire:navigate
                    wire:key="conv-{{ $conv->id }}"
                    @class([
                        'block w-full truncate rounded-lg px-3 py-2 text-left text-sm transition',
                        'bg-zinc-100 dark:bg-zinc-800' => $conversationId === $conv->id,
                        'hover:bg-zinc-50 dark:hover:bg-zinc-900' => $conversationId !== $conv->id,
                    ])
                >
                    {{ str($conv->title ?? $conv->updated_at)->limit(40) }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Mobile: Conversation Dropdown --}}
    <div class="md:hidden">
        <flux:dropdown>
            <flux:button variant="ghost" icon="chat-bubble-left-right">
                Gespräche
            </flux:button>
            <flux:menu>
                <flux:menu.item :href="route('holocron.chopper')" icon="plus" wire:navigate>
                    Neues Gespräch
                </flux:menu.item>
                <flux:separator />
                @foreach ($conversations as $conv)
                    <flux:menu.item
                        :href="route('holocron.chopper', $conv->id)"
                        wire:key="conv-mobile-{{ $conv->id }}"
                        wire:navigate
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
            x-data="{
                shouldAutoScroll: true,
                observer: null,
                scrollToBottom() {
                    this.$el.scrollTop = this.$el.scrollHeight
                },
                isNearBottom() {
                    return this.$el.scrollHeight - this.$el.scrollTop - this.$el.clientHeight < 50
                },
                init() {
                    this.observer = new MutationObserver(() => {
                        if (this.shouldAutoScroll) {
                            this.scrollToBottom()
                        }
                    })
                    this.observer.observe(this.$el, { childList: true, subtree: true, characterData: true })
                },
                destroy() {
                    this.observer?.disconnect()
                }
            }"
            @scroll="shouldAutoScroll = isNearBottom()"
            @message-sent.window="shouldAutoScroll = true; scrollToBottom()"
        >
            @if (empty($messages) && ! $isStreaming)
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
                            @if (! empty($msg['attachments']))
                                <div class="mb-2 flex flex-wrap gap-1">
                                    @foreach ($msg['attachments'] as $path)
                                        <img
                                            src="{{ Storage::disk('local')->temporaryUrl($path, now()->addDay()) }}"
                                            class="max-h-32 rounded-lg"
                                            alt="Anhang"
                                        />
                                    @endforeach
                                </div>
                            @endif
                            {{ $msg['content'] }}
                        @endif
                    </div>
                </div>
            @endforeach

            {{-- Streaming response --}}
            @if ($isStreaming)
                <div class="flex justify-start">
                    <div class="max-w-[80%] rounded-2xl bg-zinc-100 px-4 py-2 dark:bg-zinc-800">
                        <div class="prose prose-sm dark:prose-invert" wire:stream="assistant-response">
                            @if ($streamedResponse === '')
                                <flux:icon.loading class="size-5 text-zinc-400" />
                            @else
                                {!! str($streamedResponse)->markdown() !!}
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Input --}}
        <form
            wire:submit="send"
            class="mt-4"
            x-bind:class="dragOver ? 'ring-2 ring-accent rounded-xl' : ''"
            x-data="{
                dragOver: false,
                handleDrop(e) {
                    this.dragOver = false;
                    const files = [...e.dataTransfer.files].filter(f => f.type.startsWith('image/'));
                    if (files.length) {
                        $wire.uploadMultiple('attachments', files);
                    }
                },
                handlePaste(e) {
                    const items = [...(e.clipboardData?.items || [])];
                    const imageFiles = items
                        .filter(item => item.type.startsWith('image/'))
                        .map(item => item.getAsFile())
                        .filter(Boolean);
                    if (imageFiles.length) {
                        e.preventDefault();
                        $wire.uploadMultiple('attachments', imageFiles);
                    }
                },
            }"
            @dragover.prevent="dragOver = true"
            @dragleave.prevent="dragOver = false"
            @drop.prevent="handleDrop($event)"
            @paste="handlePaste($event)"
        >
            <flux:composer
                wire:model="message"
                submit="enter"
                placeholder="Nachricht an Chopper..."
            >
                <x-slot:header>
                    <div wire:loading wire:target="attachments" class="flex items-center gap-2 pb-1 text-sm text-zinc-500">
                        <flux:icon.arrow-path class="size-4 animate-spin" />
                        Bilder werden hochgeladen...
                    </div>

                    @if ($attachments)
                        <div wire:loading.remove wire:target="attachments" class="flex flex-wrap gap-2 pb-1">
                            @foreach ($attachments as $index => $attachment)
                                <div wire:key="preview-{{ $index }}" class="group relative">
                                    @if (method_exists($attachment, 'isPreviewable') && $attachment->isPreviewable())
                                        <img
                                            src="{{ $attachment->temporaryUrl() }}"
                                            class="size-16 rounded-lg object-cover"
                                            alt="Anhang {{ $index + 1 }}"
                                        />
                                    @else
                                        <div class="flex size-16 items-center justify-center rounded-lg bg-zinc-200 dark:bg-zinc-700">
                                            <flux:icon.document class="size-6" />
                                        </div>
                                    @endif
                                    <button
                                        type="button"
                                        wire:click="removeAttachment({{ $index }})"
                                        class="absolute -right-1 -top-1 hidden size-5 items-center justify-center rounded-full bg-zinc-800 text-white group-hover:flex dark:bg-zinc-200 dark:text-zinc-900"
                                    >
                                        <flux:icon.x-mark class="size-3" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-slot:header>

                <x-slot:actionsLeading>
                    <flux:button icon="photo" size="sm" variant="subtle" x-on:click="$refs.fileInput.click()" />
                    <input
                        x-ref="fileInput"
                        type="file"
                        wire:model="attachments"
                        multiple
                        accept="image/*"
                        class="hidden"
                    />
                </x-slot:actionsLeading>

                <x-slot:actionsTrailing>
                    <flux:button
                        type="submit"
                        icon="paper-airplane"
                        size="sm"
                        variant="primary"
                        wire:loading.attr="disabled"
                        wire:target="attachments,send"
                    />
                </x-slot:actionsTrailing>
            </flux:composer>
        </form>
    </div>
</div>
