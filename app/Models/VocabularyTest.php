<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VocabularyTest extends Model
{
    /** @use HasFactory<\Database\Factories\VocabularyTestFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'word_ids' => AsCollection::class,
            'correct_ids' => AsCollection::class,
            'wrong_ids' => AsCollection::class,
            'finished' => 'boolean',
        ];
    }

    protected $attributes = [
        'correct_ids' => '[]',
        'wrong_ids' => '[]',
    ];

    public function words()
    {
        return VocabularyWord::whereIn('id', $this->word_ids ?? [])->get();
    }

    public function correct()
    {
        return VocabularyWord::whereIn('id', $this->correct_ids ?? [])->get();
    }

    public function wrong()
    {
        return VocabularyWord::whereIn('id', $this->wrong_ids ?? [])->get();
    }

    public function leftWords()
    {
        $allWords = $this->words();
        $correctWords = $this->correct();

        return $allWords->diff($correctWords);
    }

    public function markAsCorrect(int $id): void
    {
        $wordIndex = $this->word_ids->search($id);

        $this->update([
            'correct_ids' => $this->correct_ids->push($this->word_ids[$wordIndex])->unique()->values(),
        ]);

        $this->checkIfTestIsFinished();
    }

    public function markAsWrong(int $id): void
    {
        $wordIndex = $this->word_ids->search($id);

        $this->update([
            'wrong_ids' => $this->wrong_ids->push($this->word_ids[$wordIndex])->unique()->values(),
        ]);

        $this->checkIfTestIsFinished();
    }

    private function checkIfTestIsFinished(): void
    {
        if ($this->word_ids->count() === $this->correct_ids->count()) {
            $this->update(['finished' => true]);
        }
    }
}
