<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Quest\Models\Note;
use Modules\Holocron\Quest\Models\Quest;

/**
 * @extends Factory<Note>
 */
class NoteFactory extends Factory
{
    protected $model = Note::class;

    public function definition(): array
    {
        return [
            'quest_id' => Quest::factory(),
            'content' => fake()->paragraph,
            'status' => null,
        ];
    }
}
