<flux:modal name="new-exercise" variant="flyout">
    <form wire:submit="submit" class="space-y-2">
        <flux:input label="Name" wire:model="form.name" />
        <flux:input label="Beschreibung" wire:model="form.description" />
        <flux:input label="Instruktionen" wire:model="form.instructions" />

        <flux:button variant="primary" type="submit">Speichern</flux:button>
    </form>
</flux:modal>
