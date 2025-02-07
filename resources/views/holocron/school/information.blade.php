@use(Illuminate\Support\Carbon)

<x-slot:title>Schule</x-slot>

<div>
    <x-heading tag="h1">Schule</x-heading>

    <div class="space-y-12">
        <section>
            <x-heading tag="h2">News</x-heading>
            @foreach($news as $newsItem)
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold">{{ $newsItem->subject }}</h3>
                    <p>{!! str($newsItem->text)->markdown() !!}</p>
                </div>
            @endforeach
        </section>
        <section>
            <x-heading tag="h2">Hausaufgaben</x-heading>

            <flux:table>
                <flux:columns>
                    <flux:column>Fach</flux:column>
                    <flux:column>Aufgegeben am</flux:column>
                    <flux:column>Fälligkeitsdatum</flux:column>
                    <flux:column>Text</flux:column>
                </flux:columns>
                <flux:rows>
                    @foreach ($homeworks as $homework)
                        <flux:row>
                            <flux:cell>{{ $homework->subject }}</flux:cell>
                            <flux:cell>{{ $homework->date->format('d.m.Y') }}</flux:cell>
                            <flux:cell>
                                {{ $homework->dueDate->format('d.m.Y') }}
                                @if ($homework->done)
                                    <flux:badge
                                        size="sm"
                                        class="ml-2"
                                        color="green"
                                    >Erledigt
                                    </flux:badge>
                                @endif
                            </flux:cell>
                            <flux:cell class="md:whitespace-normal">{!! str($homework->text)->markdown() !!}</flux:cell>
                        </flux:row>
                    @endforeach
                </flux:rows>
            </flux:table>
        </section>

        <section>
            <x-heading tag="h2">Anstehende KAs</x-heading>

            <flux:table>
                <flux:columns>
                    <flux:column>Fach</flux:column>
                    <flux:column>Datum</flux:column>
                    <flux:column>Text</flux:column>
                </flux:columns>
                <flux:rows>
                    @foreach ($exams as $exam)
                        <flux:row>
                            <flux:cell>{{ $exam->subject }}</flux:cell>
                            <flux:cell>{{ $exam->date->format('d.m.Y') }}</flux:cell>
                            <flux:cell class="whitespace-normal">{!! str($exam->text)->markdown() !!}</flux:cell>
                        </flux:row>
                    @endforeach
                </flux:rows>
            </flux:table>
        </section>

        <section>
            <x-heading tag="h2">Stundenplan</x-heading>
            <div
                x-data="calendar"
                x-ref="calendar"
            ></div>
        </section>
    </div>
</div>

@assets
<script
    defer
    src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"
></script>
@endassets

@script
<script>
    Alpine.data('calendar', () => ({
        calendar: null,
        events: {!!
                    json_encode($timetable->map(fn ($lesson) => [
                        'id' => $lesson->id,
                        'title' => $lesson->subject,
                        'start' => $lesson->start,
                        'end' => $lesson->end,
                        'backgroundColor' => $lesson->cancelled ? '#f87171' : '#60a5fa',
                        'borderColor' => $lesson->cancelled ? '#f87171' : '#60a5fa',
                    ])->values())
                !!},
        init () {
            this.calendar = new FullCalendar.Calendar(this.$refs.calendar, {
                events: (info, success) => success(this.events),
                locale: 'de',
                firstDay: 1,
                hiddenDays: [0, 6],
                headerToolbar: {
                    start: 'timeGridDay,timeGridWeek',
                    end: 'prev,next',
                },
                slotMinTime: '07:30',
                slotMaxTime: '16:30',
                initialDate: '{{ today()->isWeekday() ? today()->format('Y-m-d') : today()->nextWeekday()->format('Y-m-d') }}',
                initialView: window.innerWidth < 768 ? 'timeGridDay' : 'timeGridWeek',
            })

            this.calendar.render()
        },
    }))
</script>
@endscript
