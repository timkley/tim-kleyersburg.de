<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VocabularyWord extends Model
{
    /** @use HasFactory<\Database\Factories\VocabularyWordFactory> */
    use HasFactory;

    public function score(): int
    {
        return $this->right - $this->wrong;
    }
}
