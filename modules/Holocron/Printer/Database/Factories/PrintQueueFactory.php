<?php

declare(strict_types=1);

namespace Modules\Holocron\Printer\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Printer\Model\PrintQueue;

/**
 * @extends Factory<PrintQueue>
 */
class PrintQueueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'image' => fake()->imageUrl(),
            'actions' => [
                'https://example.com/action1',
                'https://example.com/action2',
            ],
            'printed_at' => null,
        ];
    }
}
