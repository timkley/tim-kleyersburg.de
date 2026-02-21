<?php

declare(strict_types=1);

namespace Modules\Holocron\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Holocron\User\Database\Factories\UserSettingFactory;

/**
 * @property-read float $weight
 * @property-read bool $printer_silenced
 * @property-read ?array $nutrition_daily_targets
 */
class UserSetting extends Model
{
    /** @use HasFactory<UserSettingFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): UserSettingFactory
    {
        return UserSettingFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nutrition_daily_targets' => 'array',
        ];
    }
}
