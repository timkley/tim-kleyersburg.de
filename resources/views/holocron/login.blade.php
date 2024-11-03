<x-layouts.app>
    <form
        action="{{ route('holocron.login') }}"
        method="post"
        class="mx-auto my-24 max-w-sm space-y-4"
    >
        @csrf

        <flux:input
            label="Email"
            name="email"
            type="email"
        />
        <flux:input
            label="Password"
            name="password"
            type="password"
        />

        <flux:button
            variant="primary"
            type="submit"
            >Log in</flux:button
        >
    </form>
</x-layouts.app>
