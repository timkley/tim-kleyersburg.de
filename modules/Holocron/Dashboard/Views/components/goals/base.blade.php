@props(['goal', 'streaks'])

<div>
    <flux:subheading class="flex gap-x-2 items-center">
        <flux:icon.medal class="size-4"/>
        <span>
            ×&nbsp;{{ $streaks['current'] }}
        </span>
        •
        <span>
            Top ×&nbsp;{{ $streaks['highest'] }}
        </span>
    </flux:subheading>

    <flux:heading class="mb-3 flex items-center gap-x-2">
        @if ($goal->reached)
            <flux:badge
                size="sm"
                color="sky"
                inset="top bottom"
            >
                Ziel erreicht 🎉
            </flux:badge>
        @endif

        <span>
            {{ $title }}
        </span>

        @isset($amounts)
            {{ $amounts }}
        @else
            <span> {{ $goal->amount }} / {{ $goal->goal }} {{ $goal->type->unit() }} </span>
        @endisset
    </flux:heading>

    <div class="mt-2">
        {{ $slot }}
    </div>
</div>
