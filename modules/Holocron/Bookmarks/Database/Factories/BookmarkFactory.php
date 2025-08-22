<?php

declare(strict_types=1);

namespace Modules\Holocron\Bookmarks\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Bookmarks\Models\Bookmark;
use Modules\Holocron\Bookmarks\Models\Webpage;

/**
 * @extends Factory<Bookmark>
 */
class BookmarkFactory extends Factory
{
    protected $model = Bookmark::class;

    public function definition(): array
    {
        return [
            'webpage_id' => Webpage::factory(),
        ];
    }
}
