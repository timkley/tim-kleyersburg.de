<?php

declare(strict_types=1);

namespace Modules\Holocron\Bookmarks\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Bookmarks\Models\Webpage;

/**
 * @extends Factory<Webpage>
 */
class WebpageFactory extends Factory
{
    protected $model = Webpage::class;

    public function definition(): array
    {
        return [
            'url' => fake()->url(),
            'title' => fake()->sentence,
            'description' => fake()->sentence,
            'summary' => fake()->paragraph,
        ];
    }
}
