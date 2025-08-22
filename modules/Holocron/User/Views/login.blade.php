<form
    wire:submit="login"
    class="mx-auto my-24 max-w-sm space-y-4"
>
    @csrf

    <flux:input
        label="Email"
        name="email"
        type="email"
        wire:model="email"
    />
    <flux:input
        label="Password"
        name="password"
        type="password"
        wire:model="password"
    />

    <flux:button
        variant="primary"
        type="submit"
        >Log in
    </flux:button>
</form>
