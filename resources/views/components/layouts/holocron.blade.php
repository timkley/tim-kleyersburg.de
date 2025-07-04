<x-layouts.app>
    <x-slot:title>{{ $title ?? '' }}</x-slot>
    <x-slot:header>
        <div class="mx-auto max-w-5xl print:hidden text-sm flex items-center justify-between gap-x-6">
            <a
                href="{{ route('holocron.dashboard') }}"
                wire:navigate
                class="inline-flex items-center gap-1.5 !border-0 font-mono font-semibold"
            >
                <flux:icon.cpu-chip />
                <span> Holocron </span>
            </a>

            <div class="flex items-center gap-x-4">
                <a href="{{ route('holocron.experience') }}" wire:navigate>
                    <flux:badge as="button">{{ Number::format(number: auth()->user()->experience ?? 0, locale: 'de') }} XP: Level {{ auth()->user()->level }}</flux:badge>
                </a>
                <flux:button icon="cog" href="{{ route('holocron.settings') }}" variant="ghost" inset />
            </div>
        </div>
    </x-slot>

    <div class="mx-auto mt-6 max-w-5xl mb-24">
        {{ $slot }}

        <livewire:holocron.components.bottom-navigation />
    </div>

    <x-slot:footer></x-slot>

    @persist('toast')
        <flux:toast />
    @endpersist
    <script>
        // @source https://github.com/AlpineUse/AlpineUse/blob/main/src/use-longpress/index.js
        document.addEventListener('alpine:init', () => {
            Alpine.directive(
                "longpress",
                (el, { value, expression }, { evaluateLater, cleanup }) => {
                    // Set the duration for the long press (default: 500ms)
                    const duration = value || 500;
                    let timeout;
                    const evaluate = evaluateLater(expression);

                    // Create and append custom styles for elements with the long press class
                    const styleElement = document.createElement("style");
                    styleElement.textContent = `
        .useLongpress, .useLongpress * {
          -webkit-tap-highlight-color: transparent !important;
          user-select: none !important;
          -webkit-user-select: none !important;
          -moz-user-select: none !important;
          -ms-user-select: none !important;
          -o-user-select: none !important;
          -webkit-user-drag: none !important;
          -webkit-overflow-scrolling: touch;
          scroll-behavior: smooth;
        }
      `;
                    document.head.appendChild(styleElement);
                    // Add a CSS class to the element to apply the above styles
                    el.classList.add("useLongpress");

                    // Function to prevent the context menu from appearing
                    const preventContextMenu = (event) => {
                        event.preventDefault();
                    };

                    // Function that starts the long press timer
                    const startPress = () => {
                        el.dataset.longpress = '1';
                        timeout = setTimeout(() => {
                            evaluate();
                            delete el.dataset.longpress;
                        }, duration);
                    };

                    // Function that cancels the long press timer
                    const cancelPress = () => {
                        clearTimeout(timeout);
                        delete el.dataset.longpress;
                    };

                    // Add event listeners to detect long press and prevent default context menu
                    el.addEventListener("contextmenu", preventContextMenu);
                    el.addEventListener("mousedown", startPress);
                    el.addEventListener("touchstart", startPress);
                    el.addEventListener("mouseup", cancelPress);
                    el.addEventListener("mouseleave", cancelPress);
                    el.addEventListener("touchend", cancelPress);
                    el.addEventListener("touchcancel", cancelPress);

                    // Cleanup function to remove event listeners when the directive is unmounted
                    cleanup(() => {
                        el.removeEventListener("contextmenu", preventContextMenu);
                        el.removeEventListener("mousedown", startPress);
                        el.removeEventListener("touchstart", startPress);
                        el.removeEventListener("mouseup", cancelPress);
                        el.removeEventListener("mouseleave", cancelPress);
                        el.removeEventListener("touchend", cancelPress);
                        el.removeEventListener("touchcancel", cancelPress);
                    });
                }
            );
        })
    </script>
</x-layouts.app>
