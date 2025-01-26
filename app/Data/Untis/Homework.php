<?php

declare(strict_types=1);

namespace App\Data\Untis;

use Illuminate\Support\Carbon;

class Homework
{
    public function __construct(public int $id, public string $subject, public Carbon $date, public Carbon $dueDate, public string $text, public bool $done) {}

    public static function create(int $id, string $subject, Carbon $date, Carbon $dueDate, string $text, bool $done): self
    {
        return new self($id, $subject, $date, $dueDate, $text, $done);
    }

    public static function createFromApi(array $item): self
    {
        return self::create(
            $item['id'] ?? hash('sha-256', $item['lesson']['subject'].$item['date'].$item['dueDate']),
            $item['lesson']['subject'],
            Carbon::createFromFormat('Ymd', $item['date']),
            Carbon::createFromFormat('Ymd', $item['dueDate']),
            $item['text'],
            (bool) $item['completed']
        );
    }
}
