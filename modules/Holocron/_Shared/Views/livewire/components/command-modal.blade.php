<div>
    <flux:modal name="command-modal" variant="bare" class="w-full max-w-[30rem] my-[12vh] max-h-screen overflow-y-hidden px-4">
        <flux:command class="border-none shadow-lg inline-flex flex-col max-h-[76vh]">
            <flux:command.input
                wire:model="name"
                placeholder="Create a new quest..."
                x-on:keyup.enter.prevent="
                    if ($event.shiftKey) {
                        $wire.createQuest(true)
                    } else {
                        $wire.createQuest(false)
                    }
                "
                autofocus
            />
        </flux:command>
    </flux:modal>
</div>
