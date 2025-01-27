<?php

declare(strict_types=1);

namespace App\Data\Untis;

class News
{
    public function __construct(public int $id, public string $subject, public string $text) {}

    public static function create(int $id, string $subject, string $text): self
    {
        return new self($id, $subject, $text);
    }

    public static function createFromApi(array $item): self
    {
        return self::create(
            $item['id'] ?? hash('sha-256', $item['subject'].$item['text']),
            $item['subject'],
            $item['text']
        );
    }
}
