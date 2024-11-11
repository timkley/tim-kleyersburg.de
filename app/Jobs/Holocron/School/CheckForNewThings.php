<?php

namespace App\Jobs\Holocron\School;

use App\Notifications\DiscordSchoolChannel;
use App\Notifications\Holocron\School\ClassCancelled;
use App\Notifications\Holocron\School\NewExam;
use App\Notifications\Holocron\School\NewHomework;
use App\Services\Untis;
use App\Services\Untis\Exam;
use App\Services\Untis\Homework;
use App\Services\Untis\Lesson;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Sleep;

class CheckForNewThings implements ShouldQueue
{
    use Queueable;

    public Untis $untis;

    public function __construct()
    {
        //
    }

    public function handle(Untis $untis): void
    {
        $this->untis = $untis;

        $this->checkHomeworks();
        Sleep::for(300)->milliseconds();
        $this->checkExams();
        Sleep::for(300)->milliseconds();
        $this->checkLessons();
    }

    private function checkHomeworks(): void
    {
        $homeworks = $this->untis->homeworks(today()->subDays(7), today()->addDays(14));

        $homeworks
            ->filter(fn (Homework $homework) => $homework->dueDate->isFuture())
            ->each(function (Homework $homework) {
                $key = 'holocron.school.homeworks.'.$homework->id;

                // don't notify for done homework
                if ($homework->done) {
                    return;
                }

                $cached = $this->cache()->has($key);

                // if homework is already cached, don't notify
                if ($cached) {
                    return;
                }

                $this->cache()->put($key, true, $homework->dueDate->addWeek());
                Sleep::for(300)->milliseconds();
                (new DiscordSchoolChannel)->notify(new NewHomework($homework));
            });
    }

    private function checkExams(): void
    {
        $exams = $this->untis->exams(today()->subDays(7), today()->addDays(14));

        $exams->each(function (Exam $exam) {
            $key = 'holocron.school.exams.'.$exam->id;
            $cached = $this->cache()->has($key);

            if ($cached) {
                return;
            }

            $this->cache()->put($key, true, now()->addMonth());
            Sleep::for(300)->milliseconds();
            (new DiscordSchoolChannel)->notify(new NewExam($exam));
        });
    }

    private function checkLessons(): void
    {
        $lessons = $this->untis->timetable(today(), today()->addDays(14));

        $lessons
            ->filter(fn (Lesson $lesson) => $lesson->cancelled)
            ->each(function (Lesson $lesson) {
                $key = 'holocron.school.lessons.'.$lesson->id;
                $cached = $this->cache()->has($key);

                if ($cached) {
                    return;
                }

                $this->cache()->put($key, true, $lesson->end);
                Sleep::for(300)->milliseconds();
                (new DiscordSchoolChannel)->notify(new ClassCancelled($lesson));
            });
    }

    private function cache(): Repository
    {
        return cache()->store('file_persistent');
    }
}
