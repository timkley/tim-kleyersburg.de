@use(App\Models\Holocron\Quest)
@use(App\Enums\Holocron\QuestStatus)

<div class="space-y-4">
    <div class="overflow-x-auto no-scrollbar">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('holocron.quests') }}" wire:navigate icon="home"/>
            @foreach($quest->breadcrumb()->slice(0, -1) as $crumb)
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

    <flux:card size="sm">
        <div class="flex gap-x-2 md:[--height:calc(var(--spacing)*12)]">
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

            <flux:input class:input="md:!text-lg !h-(--height)" wire:model.live.debounce="name"/>

            <flux:modal.trigger name="parent-search">
                <flux:button class="!h-(--height) px-4" icon="folder-arrow-down"></flux:button>
            </flux:modal.trigger>
        </div>

        <flux:editor
            class="mt-4"
            wire:model.live.debounce="description"
        ></flux:editor>

        <div class="grid md:grid-cols-2 gap-8 mt-8">
            <div class="space-y-2">
                <flux:heading>Links</flux:heading>

                @forelse($quest->webpages as $webpage)
                    <livewire:holocron.quests.link :$webpage :key="$webpage->id"/>
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
                    class="max-w-lg flex justify-between items-center"
                    :key="'suggestion.' . $i"
                >
                    {{ $suggestion['name'] }}

                    <flux:button x-on:click="$wire.addQuest('{{ $suggestion['name'] }}'); $el.parentElement.remove()"
                                 variant="filled"
                                 size="sm">
                        Hinzufügen
                    </flux:button>
                </flux:text>
            @endforeach

            @if($questChildren->isNotEmpty())
                <div class="space-y-2" wire:key="quest-children-container">
                    @foreach($questChildren as $childQuest)
                        <livewire:holocron.quests.item
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
            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">Notiz speichern</flux:button>
                <flux:button wire:click="generateSolution" icon="sparkles">Smarte Lösung</flux:button>
            </div>
        </form>

        @if($quest->notes->isNotEmpty())
            <div class="space-y-2">
                @foreach($quest->notes()->latest()->get() as $note)
                    <livewire:holocron.quests.note :note="$note" :key="'note.' . $note->id"/>
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

    <livewire:holocron.quests.parent-search @select="move($event.detail)" />
</div>
