<?php

declare(strict_types=1);

namespace Modules\Holocron\School\Data;

use Carbon\CarbonImmutable;

class Exam
{
    public function __construct(public int $id, public string $subject, public CarbonImmutable $date, public string $text) {}

    public static function create(int $id, string $subject, CarbonImmutable $date, string $text): self
    {
        return new self($id, $subject, $date, $text);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public static function createFromApi(array $item): self
    {
        return self::create(
            $item['id'] ?? hash('sha256', $item['subject'].$item['examDate']),
            $item['subject'],
            CarbonImmutable::createFromFormat('Ymd', $item['examDate']),
            $item['text']
        );
    }
}
