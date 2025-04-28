@use(App\Models\Holocron\Quest)
@use(App\Enums\Holocron\QuestStatus)

<div>
    <div class="space-y-4">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('holocron.quests') }}" wire:navigate icon="home"/>
            @foreach($quest->getBreadcrumb() as $crumb)
                <flux:breadcrumbs.item href="{{ route('holocron.quests', $crumb->id) }}" wire:navigate class="whitespace-nowrap">
                    {{ $crumb->name }}
                </flux:breadcrumbs.item>
            @endforeach
        </flux:breadcrumbs>

        <flux:card class="space-y-8">
            @if($quest->exists)
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <flux:input label="Name" wire:model.live="name"/>
                        <flux:textarea
                            label="Beschreibung"
                            wire:model.live="description"
                            placeholder="Beschreibung"
                        ></flux:textarea>
                    </div>

                    <div class="space-y-4">
                        <flux:radio.group wire:model.live="status" label="Status" variant="segmented">
                            @foreach(QuestStatus::cases() as $status)
                                <flux:radio
                                    value="{{ $status->value }}"
                                    :icon="$status->icon()"
                                />
                            @endforeach
                        </flux:radio.group>

                        <div
                            class="aspect-video border-dashed rounded-lg border-2 border-gray-300 p-4 grid grid-cols-4 gap-4 grid-rows-2 hover:bg-black/5 dark:hover:bg-white/5"
                            x-bind:class="{ 'border-solid': dragged, 'border-dashed': !dragged }"
                            x-data="{ dragged: false }"
                            x-on:dragover.prevent="dragged = true"
                            x-on:dragleave.prevent="dragged = false"
                            x-on:drop.prevent="$wire.upload('image', $event.dataTransfer.files[0]); dragged = false"
                        >
                            @forelse($quest->images as $image)
                                <flux:modal.trigger :name="'image.' . $image" :key="'image.' . $image">
                                    <img class="object-cover size-full rounded-md" src="{{ asset($image) }}" alt="">
                                </flux:modal.trigger>
                            @empty
                                <flux:text class="col-span-4 row-span-2 flex items-center justify-center gap-x-2">
                                    <flux:icon icon="photo" class="text-gray-400"/>
                                    Keine Bilder
                                </flux:text>
                            @endforelse
                        </div>
                        @foreach($quest->images as $image)
                            <flux:modal :name="'image.' . $image" :key="'image.' . $image">
                                <img src="{{ asset($image) }}" alt="">
                            </flux:modal>
                        @endforeach
                    </div>
                </div>
                <flux:separator text="Unter-Quests"/>
            @endif
            <div class="space-y-4">
                <div>
                    @foreach($quest->children()->get() as $childQuest)
                        <livewire:holocron.quests.item :quest="$childQuest" :key="'item.' . $childQuest->id"/>
                    @endforeach
                </div>

                <form wire:submit="addQuest" class="md:max-w-1/2">
                    <flux:input wire:model="questDraft" placeholder="Neue Quest"/>
                </form>
            </div>
        </flux:card>

        @unless($quest->exists)
            <flux:card>
                <div class="mb-3">
                    <flux:heading>Nächste Aufgaben</flux:heading>
                    <flux:text class="mt-1">Aufgaben, an denen als nächstes gearbeitet werden sollte, da sie keine Unteraufgaben haben.</flux:text>
                </div>
                @foreach(Quest::leafNodes()->get() as $leafQuest)
                    <livewire:holocron.quests.item :quest="$leafQuest" :key="'leaf-item.' . $leafQuest->id" :with-breadcrumb="true"/>
                @endforeach
            </flux:card>
        @endunless

        @if($quest->exists)
            <flux:card>
                @if($quest->has('notes'))
                    <div class="space-y-2">
                        @foreach($quest->notes()->latest()->get() as $note)
                            <livewire:holocron.quests.note :note="$note" :key="'note.' . $note->id" />
                        @endforeach
                    </div>
                @endif

                <form wire:submit="addNote" class="space-y-4 mt-3">
                    <flux:textarea wire:model="noteDraft" placeholder="Neue Notiz"/>
                    <flux:button type="submit" variant="primary">Kommentar speichern</flux:button>
                </form>
            </flux:card>
        @endif
    </div>
</div>
