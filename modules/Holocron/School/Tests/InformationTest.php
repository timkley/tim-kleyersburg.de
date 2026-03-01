<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Modules\Holocron\School\Data\Exam;
use Modules\Holocron\School\Data\Homework;
use Modules\Holocron\School\Data\Lesson;
use Modules\Holocron\School\Data\News;
use Modules\Holocron\School\Services\Untis;
use Modules\Holocron\User\Models\User;

beforeEach(function () {
    $this->untis = Mockery::mock(Untis::class);
    $this->untis->shouldReceive('news')->andReturn(new Collection([
        News::create(id: 1, subject: 'Announcement', text: 'Sports day'),
    ]));
    $this->untis->shouldReceive('timetable')->andReturn(new Collection([
        Lesson::create(id: 1, subject: 'Math', start: Carbon::parse('2026-03-10 08:00'), end: Carbon::parse('2026-03-10 08:45'), cancelled: false),
    ]));
    $this->untis->shouldReceive('homeworks')->andReturn(new Collection([
        Homework::create(id: 1, subject: 'German', date: Carbon::parse('2026-03-10'), dueDate: Carbon::parse('2026-03-17'), text: 'Page 42', done: false),
    ]));
    $this->untis->shouldReceive('exams')->andReturn(new Collection([
        Exam::create(id: 1, subject: 'Physics', date: CarbonImmutable::parse('2026-03-20'), text: 'Optics'),
    ]));

    app()->instance(Untis::class, $this->untis);
});

it('renders the information component', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('holocron.school.information')
        ->assertOk()
        ->assertSee('Announcement')
        ->assertSee('Sports day');
});

it('displays homework data', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('holocron.school.information')
        ->assertSee('German')
        ->assertSee('Page 42');
});

it('displays exam data', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('holocron.school.information')
        ->assertSee('Physics')
        ->assertSee('Optics');
});

it('renders with timetable data without errors', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('holocron.school.information')
        ->assertOk()
        ->assertSee('Stundenplan');
});

it('renders with empty data sets', function () {
    $emptyUntis = Mockery::mock(Untis::class);
    $emptyUntis->shouldReceive('news')->andReturn(new Collection);
    $emptyUntis->shouldReceive('timetable')->andReturn(new Collection);
    $emptyUntis->shouldReceive('homeworks')->andReturn(new Collection);
    $emptyUntis->shouldReceive('exams')->andReturn(new Collection);

    app()->instance(Untis::class, $emptyUntis);

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('holocron.school.information')
        ->assertOk()
        ->assertDontSee('Announcement');
});
