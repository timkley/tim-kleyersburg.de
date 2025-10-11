<?php

declare(strict_types=1);

namespace Modules\Holocron\Gear\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Gear\Enums\Property;
use Modules\Holocron\Gear\Models\Journey;

/**
 * @extends Factory<Journey>
 */
class JourneyFactory extends Factory
{
    protected $model = Journey::class;

    public function definition(): array
    {
        $applicableProperties = collect(Property::cases())
            ->filter(fn (Property $property) => $property->isJourneyApplicable());

        $selectedProperties = $this->faker->randomElements(
            $applicableProperties->toArray(),
            $this->faker->numberBetween(0, $applicableProperties->count())
        );

        return [
            'destination' => $this->faker->city,
            'starts_at' => $this->faker->dateTimeBetween('+1 day', '+1 week'),
            'ends_at' => $this->faker->dateTimeBetween('+2 weeks', '+3 weeks'),
            'properties' => collect($selectedProperties),
        ];
    }
}
