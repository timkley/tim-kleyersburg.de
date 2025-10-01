@use(Modules\Holocron\Quest\Models\Quest)

<div class="space-y-4">
    @if(!$quest->daily)
        <div class="overflow-x-auto no-scrollbar">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item>
                    <flux:modal.trigger name="parent-search">
                        <flux:button icon="folder-arrow-down" variant="ghost"/>
                    </flux:modal.trigger>
                </flux:breadcrumbs.item>
                @foreach($quest->breadcrumb() as $crumb)
                    <flux:breadcrumbs.item
                        href="{{ route('holocron.quests.show', $crumb->id) }}"
                        wire:navigate
                        wire:key="crumb.{{ $crumb->id }}"
                        class="whitespace-nowrap"
                    >
                        {{ $crumb->name }}
                    </flux:breadcrumbs.item>
                @endforeach
            </flux:breadcrumbs>
        </div>
    @endif

    <flux:card size="sm">
        <div class="flex flex-col sm:flex-row gap-2">
            <div class="flex gap-x-2 flex-1">
                @if(! $quest->daily)
                    <div class="flex items-center">
                        @if($quest->is_note)
                            <flux:button icon="document-text" variant="ghost"></flux:button>
                        @else
                            <flux:button
                                icon="{{ $quest->isCompleted() ? 'square-check-big' : 'square' }}"
                                variant="ghost"
                                wire:click="toggleComplete"
                            />
                        @endif
                    </div>
                @endif

                <flux:input class:input="md:!text-lg" wire:model.live.debounce="name"/>
            </div>

            @if(! $quest->daily)
                <div class="flex gap-x-2">
                    <flux:button
                        @class([
                            'px-4',
                            'after:absolute after:size-2 after:box-content after:rounded-full after:bg-sky-500 after:border-2 after:border-white dark:after:border-zinc-600 after:-top-1 after:-right-1' => $quest->is_note
                        ])
                        icon="document-text"
                        wire:click="toggleIsNote"
                    ></flux:button>

                    <flux:modal.trigger name="reminder-modal">
                        <flux:button
                            @class([
                                'px-4 relative',
                                'after:absolute after:size-2 after:box-content after:rounded-full after:bg-sky-500 after:border-2 after:border-white dark:after:border-zinc-600 after:-top-1 after:-right-1' => $this->activeReminders->isNotEmpty()
                            ])
                            icon="bell"
                        />
                    </flux:modal.trigger>

                    @if(!$quest->instanceOf)
                        <flux:modal.trigger name="recurrence-modal">
                            <flux:button
                                @class([
                                    'px-4 relative',
                                    'after:absolute after:size-2 after:box-content after:rounded-full after:bg-sky-500 after:border-2 after:border-white dark:after:border-zinc-600 after:-top-1 after:-right-1' => $quest->recurrence
                                ])
                                icon="history"
                            />
                        </flux:modal.trigger>
                    @else
                        <flux:button
                            @class([
                                'px-4 relative',
                            ])
                            icon="history"
                            href="{{ route('holocron.quests.show', $quest->instanceOf->quest_id) }}"
                            wire:navigate
                        />
                    @endif

                    <flux:button
                        @class([
                            'px-4',
                            'relative after:absolute after:size-2 after:box-content after:rounded-full after:bg-sky-500 after:border-2 after:border-white dark:after:border-zinc-600 after:-top-1 after:-right-1' => $quest->should_be_printed
                        ])
                        icon="printer"
                        wire:click="print"
                    />

                    <flux:date-picker
                        class="flex-1"
                        wire:model.live="date"
                        start-day="1"
                        locale="de-DE"
                        with-today
                        clearable />
                </div>
            @endif
        </div>

        <flux:editor
            class="mt-4"
            wire:model.live.debounce="description"
        ></flux:editor>

        <div class="grid md:grid-cols-2 gap-8 mt-8">
            <div class="space-y-2">
                <flux:heading>Links</flux:heading>

                @forelse($quest->webpages as $webpage)
                    <livewire:holocron.quest.components.link :$webpage :key="$webpage->id"/>
                @empty
                    <flux:text>Keine Links</flux:text>
                @endforelse

                <flux:input
                    class="mt-4"
                    wire:model="linkDraft"
                    wire:keydown.enter="addLink"
                    placeholder="https://"
                />
            </div>

            <div
                class="space-y-2"
                x-data
            >
                <flux:file-upload wire:model="newAttachments" multiple>
                    <flux:file-upload.dropzone
                        icon="photo"
                        heading="Anhänge hochladen"
                        text="Ziehe deine Bilder hierher oder klicke, um sie auszuwählen."
                    />
                </flux:file-upload>

                <div class="space-y-2 mt-4">
                    @foreach($quest->attachments as $attachment)
                        <a class="block" href="{{ asset('storage/' . $attachment) }}">
                            <flux:file-item
                                :key="$attachment"
                                :image="asset('storage/' . $attachment)"
                            >
                                <x-slot:actions>
                                    <flux:file-item.remove wire:click="removeAttachment('{{ $attachment }}')" />
                                </x-slot:actions>
                            </flux:file-item>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-12 mb-6">
            <flux:separator text="Unter-Quests"/>
        </div>

        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <form wire:submit="addQuest" class="flex-1">
                    <flux:input wire:model="questDraft" placeholder="Neue Quest"/>
                </form>
                <flux:button icon="sparkles" wire:click="generateSubquests">
                    Quests vorschlagen
                </flux:button>
            </div>

            @foreach($subquestSuggestions as $i => $suggestion)
                <flux:text
                    class="flex justify-between items-center"
                    :key="'suggestion.' . $i"
                >
                    {{ $suggestion }}

                    <flux:button
                        x-on:click="$wire.addQuest('{{ $suggestion }}'); $el.parentElement.remove()"
                        variant="filled"
                        size="sm"
                    >
                        Hinzufügen
                    </flux:button>
                </flux:text>
            @endforeach

            @if($questChildren->isNotEmpty())
                <div class="space-y-2" wire:key="quest-children-container">
                    @foreach($questChildren as $childQuest)
                        <livewire:holocron.quest.components.item
                            :quest="$childQuest"
                            :key="'item.' . $childQuest->id"
                            :show-parent="false"
                        />
                    @endforeach
                </div>
            @endif

            <div class="ml-auto w-fit mr-2">
                <flux:switch label="Alle Unter-Quests" wire:model.live="showAllSubquests"/>
            </div>
        </div>
    </flux:card>

    <flux:card class="space-y-3" size="sm">
        <form wire:submit="addNote" class="space-y-4">
            <flux:editor wire:model="noteDraft" placeholder="Neue Notiz"></flux:editor>
            <div class="flex justify-between items-center gap-2">
                <div class="flex gap-x-2">
                    <flux:button type="submit" variant="primary">Speichern</flux:button>
                </div>

                <div>
                    <flux:switch wire:model.live="chat" label="Chat"/>
                </div>
            </div>
        </form>

        @if($quest->notes->isNotEmpty())
            <div class="space-y-2">
                @foreach($quest->notes()->latest()->get() as $note)
                    <livewire:holocron.quest.components.note :note="$note" :key="'note.' . $note->id"/>
                @endforeach
            </div>
        @endif
    </flux:card>



    <livewire:holocron.quest.components.parent-search @select="move($event.detail)"/>

    @if(!$quest->instanceOf)
        @include('holocron-quest::components.recurrence-modal')
    @endif
    @include('holocron-quest::components.reminder-modal')
</div>
