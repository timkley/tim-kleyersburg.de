<?php

declare(strict_types=1);

namespace App\Models\Holocron\School;

use Database\Factories\Holocron\School\VocabularyTestFactory;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class VocabularyTest extends Model
{
    /** @use HasFactory<VocabularyTestFactory> */
    use HasFactory;

    protected $attributes = [
        'correct_ids' => '[]',
        'wrong_ids' => '[]',
    ];

    private ?Collection $cachedWords = null;

    public function words()
    {
        if ($this->cachedWords === null) {
            $this->cachedWords = VocabularyWord::whereIn('id', $this->word_ids ?? [])->get();
        }

        return $this->cachedWords;
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
        $word = VocabularyWord::find($id);
        $word->increment('right');
        $wordIndex = $this->word_ids->search($id);

        $this->update([
            'correct_ids' => $this->correct_ids->push($this->word_ids[$wordIndex])->unique()->values(),
        ]);

        $this->checkIfTestIsFinished();
    }

    public function markAsWrong(int $id): void
    {
        $word = VocabularyWord::find($id);
        $word->increment('wrong');
        $wordIndex = $this->word_ids->search($id);

        $this->update([
            'wrong_ids' => $this->wrong_ids->push($this->word_ids[$wordIndex])->unique()->values(),
        ]);

        $this->checkIfTestIsFinished();
    }

    /**
     * @return array{
     *     word_ids: 'Illuminate\Database\Eloquent\Casts\AsCollection',
     *     correct_ids: 'Illuminate\Database\Eloquent\Casts\AsCollection',
     *     wrong_ids: 'Illuminate\Database\Eloquent\Casts\AsCollection',
     *     finished: 'boolean'
     * }
     */
    protected function casts(): array
    {
        return [
            'word_ids' => AsCollection::class,
            'correct_ids' => AsCollection::class,
            'wrong_ids' => AsCollection::class,
            'finished' => 'boolean',
        ];
    }

    private function checkIfTestIsFinished(): void
    {
        if ($this->word_ids->count() === $this->correct_ids->count()) {
            $this->update(['finished' => true]);
        }
    }
}
