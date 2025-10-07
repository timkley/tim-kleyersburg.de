@use(Illuminate\Support\Number)
@use(Illuminate\Support\Facades\Storage)

<div class="space-y-6">
    <flux:heading size="xl">
        {{ Number::format(number: $printQueue->total(), locale: 'de-DE') }} Print Queue Items
    </flux:heading>

    @if($printQueue->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($printQueue as $item)
                @php
                    $imageExists = Storage::disk('public')->exists($item->image);
                    $imageUrl = $imageExists ? Storage::url($item->image) : null;
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                    <flux:modal name="image-{{ $item->id }}" class="max-w-none">
                        <div class="bg-white">
                            @if($imageExists)
                                <img src="{{ $imageUrl }}" alt="Print Queue Item #{{ $item->id }}" class="py-8">
                            @else
                                <div class="w-full h-96 bg-gray-100 flex items-center justify-center">
                                    <div class="text-center text-gray-500">
                                        <flux:icon.photo class="w-16 h-16 mx-auto mb-2"/>
                                        <p>Image not available</p>
                                        <p class="text-xs mt-1">{{ $item->image }}</p>
                                    </div>
                                </div>
                            @endif
                            <div class="p-4 space-y-2 bg-white">
                                <div class="text-sm text-gray-600">
                                    <strong>Created:</strong> {{ $item->created_at->setTimezone(config('app.timezone'))->format('d.m.Y H:i') }}
                                </div>
                                @if($item->printed_at)
                                    <div class="text-sm text-green-600">
                                        <strong>Printed:</strong> {{ $item->printed_at->setTimezone(config('app.timezone'))->format('d.m.Y H:i') }}
                                    </div>
                                @endif
                                @if(!empty($item->actions))
                                    <div class="text-sm text-gray-600">
                                        <strong>Actions:</strong>
                                        <div class="mt-1 space-y-1">
                                            @foreach($item->actions as $action)
                                                <a href="{{ $action }}"
                                                   class="inline-block px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded hover:bg-blue-200 transition-colors">
                                                    {{ parse_url($action, PHP_URL_PATH) }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </flux:modal>

                    <flux:modal.trigger name="image-{{ $item->id }}">
                        <div class="cursor-pointer aspect-video bg-white overflow-hidden">
                            @if($imageExists)
                                <img src="{{ $imageUrl }}"
                                     alt="Print Queue Item #{{ $item->id }}"
                                     class="hover:scale-105 transition-transform duration-200 py-4">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gray-100 dark:bg-gray-700">
                                    <div class="text-center text-gray-500 dark:text-gray-400">
                                        <flux:icon.photo class="w-8 h-8 mx-auto mb-1"/>
                                        <p class="text-xs">Missing</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </flux:modal.trigger>

                    <div class="p-4 space-y-2">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <strong>Created:</strong> {{ $item->created_at->setTimezone(config('app.timezone'))->format('d.m.Y H:i') }}
                        </div>

                        @if($item->printed_at)
                            <div class="flex items-center space-x-2">
                                <flux:icon.check class="w-4 h-4 text-green-500"/>
                                <span class="text-sm text-green-600 dark:text-green-400">
                                    Printed: {{ $item->printed_at->setTimezone(config('app.timezone'))->format('d.m.Y H:i') }}
                                </span>
                            </div>
                        @else
                            <div class="flex items-center space-x-2">
                                <flux:icon.clock class="w-4 h-4 text-yellow-500"/>
                                <span class="text-sm text-yellow-600 dark:text-yellow-400">Pending</span>
                            </div>
                        @endif

                        @if(!empty($item->actions))
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ count($item->actions) }} action(s) available
                            </div>
                        @endif
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
