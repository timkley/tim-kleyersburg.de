@use(Illuminate\Support\Number)

<div class="space-y-4">
    <flux:heading size="xl">
        {{ Number::format(number: $count, locale: 'de-DE') }} Health Records
    </flux:heading>

    <div class="space-y-1">
        @foreach($entries as $entry)
            <div class="flex justify-between items-center">
                <div class="font-mono">{{ $entry->date->format('d.m.Y') }}</div>
                <div class="flex-1 ml-4">
                    <span class="font-medium">{{ $entry->name }}:</span>
                    <span>{{ $entry->qty }} {{ $entry->units }}</span>
                </div>
                <div class="font-mono text-sm text-gray-600">
                    {{ $entry->source ?? 'Unknown' }}
                </div>
            </div>
        @endforeach

        @if($entries->isEmpty())
            <div class="text-center py-8 text-gray-500">
                No health data records found.
            </div>
        @endif
    </div>

    @if($entries->hasPages())
        <flux:pagination :paginator="$entries" />
    @endif
</div>
