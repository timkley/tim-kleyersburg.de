<div class="space-y-4 max-w-md">
    @foreach($experiences as $experience)
        <div class="space-y-1">
            <div class="flex items-center justify-between gap-x-2">
                <div class="flex items-center gap-x-1">
                    <flux:icon class="size-4" icon="{{ $experience->type->icon() }}" />
                    <flux:text class="font-bold">{{ $experience->type->label() }}</flux:text>
                </div>
                <flux:text class="font-bold">{{ $experience->amount }}&nbsp;XP</flux:text>
            </div>
            <flux:text>{{ $experience->type->description() }}</flux:text>
            <flux:text class="text-xs">{{ $experience->created_at->format('d.m.Y H:i') }}</flux:text>
        </div>
    @endforeach

    <flux:pagination :paginator="$experiences" />
</div>
