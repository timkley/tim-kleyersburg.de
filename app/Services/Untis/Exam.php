<?php

namespace App\Services\Untis;

use Illuminate\Support\Carbon;

class Exam
{
    public function __construct(public int $id, public string $subject, public Carbon $date, public string $text)
    {
    }

    public static function create(int $id, string $subject, Carbon $date, string $text): self
    {
        return new self($id, $subject, $date, $text);
    }

    public static function createFromApi(array $item)
    {
        return self::create(
            $item['id'] ?? hash('sha-256', $item['subject'].$item['examDate']),
            $item['subject'],
            \Illuminate\Support\Carbon::createFromFormat('Ymd', $item['examDate']),
            $item['text']
        );
    }
}
