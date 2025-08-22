<?php

declare(strict_types=1);

namespace Modules\Holocron\School\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\School\Models\VocabularyTest;

/**
 * @extends Factory<VocabularyTest>
 */
class VocabularyTestFactory extends Factory
{
    protected $model = VocabularyTest::class;

    public function definition(): array
    {
        return [
            'word_ids' => [],
            'finished' => false,
        ];
    }
}
