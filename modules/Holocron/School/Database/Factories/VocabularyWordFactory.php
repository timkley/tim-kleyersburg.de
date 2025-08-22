<?php

declare(strict_types=1);

namespace Modules\Holocron\School\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\School\Models\VocabularyWord;

/**
 * @extends Factory<\Modules\Holocron\School\Models\VocabularyWord>
 */
class VocabularyWordFactory extends Factory
{
    protected $model = VocabularyWord::class;

    public function definition(): array
    {
        return [
            'english' => $this->faker->word(),
            'german' => $this->faker->word(),
            'right' => 0,
            'wrong' => 0,
        ];
    }
}
