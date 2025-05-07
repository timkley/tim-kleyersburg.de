<div>
    @if($lastFinishedSet)
        <div class="px-3 py-2 bg-white shadow rounded-full w-fit font-semibold mx-auto"
            x-data="{
        finished_at: '{{ $lastFinishedSet->finished_at?->toISOString() }}',
        minutes: '00',
        seconds: '00',

        get elapsed() {
            return this.minutes + ':' + this.seconds;
        },

        updateCounter() {
            const diff = Math.floor((new Date() - new Date(this.finished_at)) / 1000);
            this.minutes = String(Math.floor(diff / 60)).padStart(2, '0');
            this.seconds = String(diff % 60).padStart(2, '0');
        },

        init() {
            if (! this.finished_at) return;
            this.updateCounter();
            setInterval(() => this.updateCounter(), 1000);
        }
    }"
        >
            <span x-text="elapsed"></span>
        </div>
    @endif
</div>
