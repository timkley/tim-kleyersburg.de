@use(Illuminate\Support\Number)
@use(Illuminate\Support\Facades\Storage)

<div class="space-y-6">
    <flux:heading size="xl">
        {{ Number::format(number: $printQueue->total(), locale: 'de-DE') }} Druckaufträge
    </flux:heading>

    @if($printQueue->count() > 0)
        <div class="space-y-6">
            @foreach($printQueue as $item)
                @php
                    $imageExists = Storage::disk('public')->exists($item->image);
                    $imageUrl = $imageExists ? Storage::url($item->image) : null;
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden max-w-sm mx-auto">
                    @if($imageExists)
                        <img src="{{ $imageUrl }}"
                             alt="Print Queue Item #{{ $item->id }}"
                             class="p-4 bg-white rounded-lg">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gray-100 dark:bg-gray-700">
                            <div class="text-center text-gray-500 dark:text-gray-400">
                                <flux:icon.photo class="w-8 h-8 mx-auto mb-1"/>
                                <p class="text-xs">Missing</p>
                            </div>
                        </div>
                    @endif

                    <div class="px-4 space-y-2 my-4">
                        <div>
                            <flux:badge :icon="$item->printed_at ? 'printer' : 'clock'"
                                        :color="$item->printed_at ? 'lime' : 'orange'">
                                {{ $item->printed_at?->format('d.m.Y H:i') ?? 'Pending' }}
                            </flux:badge>
                        </div>
                        <div>
                            <flux:badge color="sky">{{ $item->length() }} mm</flux:badge>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            <flux:pagination :paginator="$printQueue"/>
        </div>
    @else
        <div class="text-center py-12">
            <flux:icon.printer class="w-16 h-16 text-gray-400 mx-auto mb-4"/>
            <flux:heading size="lg" class="text-gray-500 dark:text-gray-400">No items in print queue</flux:heading>
            <p class="text-gray-400 dark:text-gray-500 mt-2">Print queue items will appear here when added.</p>
        </div>
    @endif
</div>
