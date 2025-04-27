@use(App\Models\Holocron\Quest)
@use(App\Enums\Holocron\QuestStatus)

<div>
    <div class="space-y-4">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('holocron.quests') }}" wire:navigate icon="home"/>
            @foreach($quest->getBreadcrumb() as $crumb)
                <flux:breadcrumbs.item href="{{ route('holocron.quests', $crumb->id) }}" wire:navigate>
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

                    <div>
                        <flux:radio.group wire:model.live="status" label="Status" variant="segmented">
                            @foreach(QuestStatus::cases() as $status)
                                <flux:radio
                                    label="{{ $status->label() }}"
                                    value="{{ $status->value }}"
                                    :icon="$status->icon()"
                                />
                            @endforeach
                        </flux:radio.group>
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

                <form wire:submit="addQuest" class="max-w-1/2">
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
