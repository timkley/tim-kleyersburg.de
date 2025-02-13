<?php

declare(strict_types=1);

namespace App\Jobs\Holocron\School;

use App\Data\Untis\Exam;
use App\Data\Untis\Homework;
use App\Data\Untis\Lesson;
use App\Data\Untis\News;
use App\Notifications\DiscordSchoolChannel;
use App\Notifications\Holocron\School\ClassCancelled;
use App\Notifications\Holocron\School\NewExam;
use App\Notifications\Holocron\School\NewHomework;
use App\Notifications\Holocron\School\NewNews;
use App\Services\Untis;
use Carbon\CarbonImmutable;
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

        $this->checkNews();
        Sleep::for(300)->milliseconds();
        $this->checkHomeworks();
        Sleep::for(300)->milliseconds();
        $this->checkExams();
        Sleep::for(300)->milliseconds();
        $this->checkLessons();
    }

    private function checkNews(): void
    {
        $news = $this->untis->news();

        $news->each(function (News $news) {
            $key = 'holocron.school.news.'.$news->id;
            $cached = $this->cache()->has($key);

            if ($cached) {
                return;
            }

            $this->cache()->put($key, true, now()->addYear());
            Sleep::for(300)->milliseconds();
            (new DiscordSchoolChannel)->notify(new NewNews($news));
        });
    }

    private function checkHomeworks(): void
    {
        $homeworks = $this->untis->homeworks(CarbonImmutable::today()->subDays(7), CarbonImmutable::today()->addDays(14));

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

                $this->cache()->put($key, true, $homework->dueDate->clone()->addWeek());
                Sleep::for(300)->milliseconds();
                (new DiscordSchoolChannel)->notify(new NewHomework($homework));
            });
    }

    private function checkExams(): void
    {
        $exams = $this->untis->exams(CarbonImmutable::today()->subDays(7), CarbonImmutable::today()->addDays(14));

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
        $lessons = $this->untis->timetable(CarbonImmutable::today(), CarbonImmutable::today()->addDays(14));

        $lessons
            ->filter(fn (Lesson $lesson) => $lesson->cancelled)
            ->filter(fn (Lesson $lesson) => $lesson->end->isFuture())
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
