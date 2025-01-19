@props(['goal'])

<div>
    <flux:heading class="mb-3 flex items-center gap-x-3">
        <span>
            {{ $title }}
        </span>

        <span> {{ $goal->amount }} / {{ $goal->goal }} {{ $goal->type->unit() }} </span>

        @if ($goal->reached)
            <flux:badge color="lime" inset>Ziel erreicht</flux:badge>
        @endif
    </flux:heading>

    {{ $slot }}
</div>
