@use(Illuminate\Support\Carbon)

<x-slot:title>Schule</x-slot>

<div>
    <x-heading tag="h1">Schule</x-heading>

    <div class="space-y-12">
        <section>
            <x-heading tag="h2">Stundenplan</x-heading>
            <div
                x-data="calendar"
                x-ref="calendar"
            ></div>
        </section>

        @if($news->count())
            <section>
                <x-heading tag="h2">News</x-heading>
                @foreach($news as $newsItem)
                    <div class="space-y-2">
                        <h3 class="text-lg font-semibold">{{ $newsItem->subject }}</h3>
                        <p>{!! str($newsItem->text)->markdown() !!}</p>
                    </div>
                @endforeach
            </section>
        @endif

        <section>
            <x-heading tag="h2">Hausaufgaben</x-heading>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Fach</flux:table.column>
                    <flux:table.column>Aufgegeben am</flux:table.column>
                    <flux:table.column>Fälligkeitsdatum</flux:table.column>
                    <flux:table.column>Text</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($homeworks as $homework)
                        <flux:table.row>
                            <flux:table.cell>{{ $homework->subject }}</flux:table.cell>
                            <flux:table.cell>{{ $homework->date->format('d.m.Y') }}</flux:table.cell>
                            <flux:table.cell>
                                {{ $homework->dueDate->format('d.m.Y') }}
                                @if ($homework->done)
                                    <flux:badge
                                        size="sm"
                                        class="ml-2"
                                        color="green"
                                    >Erledigt
                                    </flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="md:whitespace-normal">{!! str($homework->text)->markdown() !!}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </section>

        <section>
            <x-heading tag="h2">Anstehende KAs</x-heading>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Fach</flux:table.column>
                    <flux:table.column>Datum</flux:table.column>
                    <flux:table.column>Text</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($exams as $exam)
                        <flux:table.row>
                            <flux:table.cell>{{ $exam->subject }}</flux:table.cell>
                            <flux:table.cell>{{ $exam->date->format('d.m.Y') }}</flux:table.cell>
                            <flux:table.cell class="whitespace-normal">{!! str($exam->text)->markdown() !!}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
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
                slotMinTime: '07:45',
                slotMaxTime: '15:35',
                initialDate: '{{ today()->isWeekday() ? today()->format('Y-m-d') : today()->nextWeekday()->format('Y-m-d') }}',
                initialView: window.innerWidth < 768 ? 'timeGridDay' : 'timeGridWeek',
                height: 555,
                allDaySlot: false
            })

            this.calendar.render()
        },
    }))
</script>
@endscript
