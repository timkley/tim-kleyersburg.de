<div class="space-y-6">
    {{-- Date Navigation Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <flux:button wire:click="previousDay" variant="ghost" icon="chevron-left" size="sm">
                Previous
            </flux:button>

            <div class="flex items-center gap-2">
                <flux:heading size="lg">{{ $selectedDate->format('l, F j, Y') }}</flux:heading>
                @if(!$selectedDate->isToday())
                    <flux:button wire:click="today" variant="outline" size="sm">
                        Today
                    </flux:button>
                @endif
            </div>

            <flux:button wire:click="nextDay" variant="ghost" icon="chevron-right" size="sm">
                Next
            </flux:button>
        </div>

        {{-- Date Picker --}}
        <flux:field>
            <flux:input
                type="date"
                wire:model.live="selectedDate"
                wire:change="setQuestDate($event.target.value)"
            />
        </flux:field>
    </div>

    {{-- Add New Quest Form --}}
    <flux:card>
        <flux:heading size="md" class="mb-4">Add Quest for {{ $selectedDate->format('M j') }}</flux:heading>

        <form wire:submit="addQuest" class="space-y-4">
            <flux:field>
                <flux:label>Quest Name</flux:label>
                <flux:input
                    wire:model="questDraft"
                    placeholder="What do you want to accomplish today?"
                />
                <flux:error name="questDraft" />
            </flux:field>

            <flux:button type="submit" variant="primary">
                Add Quest
            </flux:button>
        </form>
    </flux:card>

    {{-- Daily Quests List --}}
    @if($quests->count() > 0)
        <div class="space-y-4">
            <flux:heading size="md">
                Quests for {{ $selectedDate->format('M j') }} ({{ $quests->count() }})
            </flux:heading>

            <div class="space-y-3">
                @foreach($quests as $quest)
                    <flux:card class="hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <flux:checkbox
                                    wire:click="toggleQuestStatus({{ $quest->id }})"
                                    :checked="$quest->status->value === 'complete'"
                                />

                                <div class="flex-1">
                                    <flux:heading size="sm"
                                        class="{{ $quest->status->value === 'complete' ? 'line-through text-gray-500' : '' }}">
                                        {{ $quest->name }}
                                    </flux:heading>

                                    @if($quest->description)
                                        <flux:text class="text-sm text-gray-600 mt-1">
                                            {{ $quest->description }}
                                        </flux:text>
                                    @endif

                                    <flux:text class="text-xs text-gray-400 mt-1">
                                        Created {{ $quest->created_at->format('g:i A') }}
                                    </flux:text>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <flux:badge
                                    :color="match($quest->status->value) {
                                        'complete' => 'green',
                                        'in_progress' => 'blue',
                                        default => 'gray'
                                    }"
                                    size="sm"
                                >
                                    {{ ucfirst(str_replace('_', ' ', $quest->status->value)) }}
                                </flux:badge>

                                <flux:button
                                    href="{{ route('holocron.quests.show', $quest) }}"
                                    variant="ghost"
                                    size="sm"
                                    icon="arrow-top-right-on-square"
                                >
                                    View
                                </flux:button>
                            </div>
                        </div>
                    </flux:card>
                @endforeach
            </div>
        </div>
    @else
        <flux:card>
            <div class="text-center py-8">
                <flux:icon name="calendar-days" size="lg" class="text-gray-400 mx-auto mb-3" />
                <flux:heading size="md" class="text-gray-600 mb-2">
                    No quests for {{ $selectedDate->format('M j') }}
                </flux:heading>
                <flux:text class="text-gray-500">
                    Add your first quest above to get started!
                </flux:text>
            </div>
        </flux:card>
    @endif
</div>
