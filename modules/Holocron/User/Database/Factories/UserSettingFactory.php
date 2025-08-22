<?php

declare(strict_types=1);

namespace Modules\Holocron\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\User\Models\UserSetting;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Holocron\User\Models\UserSetting>
 */
class UserSettingFactory extends Factory
{
    protected $model = UserSetting::class;

    public function definition(): array
    {
        return [
            'weight' => fake()->randomFloat(2, 50, 100),
        ];
    }
}
