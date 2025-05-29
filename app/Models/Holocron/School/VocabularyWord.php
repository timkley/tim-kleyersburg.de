<?php

declare(strict_types=1);

namespace App\Models\Holocron\School;

use Database\Factories\Holocron\School\VocabularyWordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VocabularyWord extends Model
{
    /** @use HasFactory<VocabularyWordFactory> */
    use HasFactory;

    public function score(): int
    {
        return $this->right - $this->wrong;
    }
}
