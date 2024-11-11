<?php

namespace App\Services\Untis;

use Illuminate\Support\Carbon;

class Lesson
{
    public function __construct(public int $id, public string $subject, public Carbon $start, public Carbon $end, public bool $cancelled)
    {
    }

    public static function create(int $id, string $subject, Carbon $start, Carbon $end, bool $cancelled): self
    {
        return new self($id, $subject, $start, $end, $cancelled);
    }

    public static function createFromApi(array $item)
    {
        $subject = data_get($item, 'su.0.longname') ?? data_get($item, 'lstext');

        return self::create(
            $item['id'] ?? hash('sha-256', $subject.$item['start']),
            $subject,
            Carbon::createFromFormat('Ymd Hi', $item['date'].' '.str_pad($item['startTime'], 4, '0', STR_PAD_LEFT)),
            Carbon::createFromFormat('Ymd Hi', $item['date'].' '.str_pad($item['endTime'], 4, '0', STR_PAD_LEFT)),
            data_get($item, 'code') === 'cancelled',
        );
    }
}