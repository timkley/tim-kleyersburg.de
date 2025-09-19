@use(Modules\Holocron\Quest\Models\Quest)
@use(Modules\Holocron\Quest\Enums\QuestStatus)

<div class="space-y-4">
    @if(!$quest->daily)
        <div class="overflow-x-auto no-scrollbar">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('holocron.quests') }}" wire:navigate icon="home"/>
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
                    <flux:dropdown class="flex items-center">
                        <flux:button :icon="$quest->status->icon()" variant="ghost"></flux:button>

                        <flux:menu wire:replace>
                            @foreach(QuestStatus::cases() as $status)
                                <flux:menu.item
                                    :icon="$status->icon()"
                                    :disabled="$status->value === $quest->status->value"
                                    wire:click="setStatus('{{ $status->value }}')"
                                    wire:key="status.{{ $status->value }}"
                                >
                                    {{ $status->label() }}
                                </flux:menu.item>
                            @endforeach
                        </flux:menu>
                    </flux:dropdown>
                @endif

                <flux:input class:input="md:!text-lg" wire:model.live.debounce="name"/>
            </div>

            @if(! $quest->daily)
                <div class="flex gap-x-2">
                    <flux:modal.trigger name="parent-search">
                        <flux:button class="px-4" icon="folder-arrow-down"></flux:button>
                    </flux:modal.trigger>

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
                class="border-dashed rounded-lg border-2 border-gray-300 p-4 grid grid-cols-4 gap-4 grid-rows-2 hover:bg-black/5 dark:hover:bg-white/5"
                x-bind:class="{ 'border-solid': dragged, 'border-dashed': !dragged }"
                x-data="{ dragged: false }"
                x-on:click="$refs.fileInput.click()"
                x-on:dragover.prevent="dragged = true"
                x-on:dragleave.prevent="dragged = false"
                x-on:drop.prevent="$wire.upload('image', $event.dataTransfer.files[0]); dragged = false"
            >
                @forelse($quest->images as $image)
                    <flux:modal.trigger :name="'image.' . $image" :key="'image.' . $image" x-on:click.stop="">
                        <img class="object-cover size-full rounded-md" src="{{ asset('storage/'.$image) }}" alt="">
                    </flux:modal.trigger>
                @empty
                    <flux:text class="col-span-4 row-span-2 flex items-center justify-center gap-x-2">
                        <flux:icon icon="photo" class="text-gray-400"/>
                        Keine Bilder
                    </flux:text>
                @endforelse
                <input
                    type="file"
                    accept="image/*"
                    class="hidden"
                    x-ref="fileInput"
                    x-on:change="$wire.upload('image', $event.target.files[0])"
                >
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

    @foreach($quest->images as $image)
        <flux:modal
            :name="'image.' . $image"
            wire:key="image.{{ $image }}"
        >
            <img src="{{ asset('storage/' . $image) }}" alt="">
        </flux:modal>
    @endforeach

    <livewire:holocron.quest.components.parent-search @select="move($event.detail)"/>

    @if(!$quest->instanceOf)
        @include('holocron-quest::components.recurrence-modal')
    @endif
    @include('holocron-quest::components.reminder-modal')
</div>
