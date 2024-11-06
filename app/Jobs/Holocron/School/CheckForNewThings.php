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
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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
        $this->checkExams();
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

                $cached = cache()->has($key);

                // if homework is already cached, don't notify
                if ($cached) {
                    return;
                }

                cache()->put($key, true, $homework->dueDate->addWeek());
                (new DiscordSchoolChannel)->notify(new NewHomework($homework));
            });
    }

    private function checkExams(): void
    {
        $exams = $this->untis->exams(today()->subDays(7), today()->addDays(14));

        $exams->each(function (Exam $exam) {
            $key = 'holocron.school.exams.'.$exam->id;
            $cached = cache()->has($key);

            if ($cached) {
                return;
            }

            cache()->put($key, true, now()->addMonth());
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
                $cached = cache()->has($key);

                if ($cached) {
                    return;
                }

                cache()->put($key, true, $lesson->end);
                (new DiscordSchoolChannel)->notify(new ClassCancelled($lesson));
            });
    }
}
