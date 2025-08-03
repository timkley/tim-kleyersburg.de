<div class="space-y-8">
    @include('holocron.gear.navigation')

    <flux:table>
        <flux:table.rows>
            @foreach($categories as $category)
                <livewire:holocron.gear.categories.components.category :$category :key="$category->id" />
            @endforeach
            <form wire:submit.prevent="submit">
                <flux:table.row>
                    <flux:table.cell>
                        <flux:input wire:model="name" name="name" placeholder="Name der neuen Kategorie"/>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:button variant="primary" type="submit">Erstellen</flux:button>
                    </flux:table.cell>
                </flux:table.row>
            </form>
        </flux:table.rows>
    </flux:table>
</div>
