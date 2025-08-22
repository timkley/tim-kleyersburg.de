<?php

declare(strict_types=1);

namespace Modules\Holocron\School\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Holocron\School\Database\Factories\VocabularyWordFactory;

/**
 * @property-read int $right
 * @property-read int $wrong
 * @property-read string $german
 * @property-read string $english
 */
class VocabularyWord extends Model
{
    /** @use HasFactory<VocabularyWordFactory> */
    use HasFactory;

    public function score(): int
    {
        return $this->right - $this->wrong;
    }

    protected static function newFactory(): VocabularyWordFactory
    {
        return VocabularyWordFactory::new();
    }
}
