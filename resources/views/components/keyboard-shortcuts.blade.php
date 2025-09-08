<div
    x-data="{
        shortcuts: {
            'gd': '{{ route('holocron.dashboard') }}',
            'gq': '{{ route('holocron.quests') }}',
        },
        keySequence: '',
        keyTimeout: null,
        init() {
            document.addEventListener('keydown', (event) => {
                const target = event.target;

                if (
                    target.tagName === 'INPUT' ||
                    target.tagName === 'TEXTAREA' ||
                    target.isContentEditable
                ) {
                    return;
                }

                if (event.metaKey || event.ctrlKey || event.altKey) {
                    this.keySequence = '';
                    return;
                }

                this.keySequence += event.key;

                clearTimeout(this.keyTimeout);
                this.keyTimeout = setTimeout(() => {
                    this.keySequence = '';
                }, 1000);

                for (const shortcut in this.shortcuts) {
                    if (this.keySequence.endsWith(shortcut)) {
                        window.Livewire.navigate(this.shortcuts[shortcut]);
                        this.keySequence = '';
                        return;
                    }
                }
            });
        }
    }"
    x-init="init()"
></div>