<?php

declare(strict_types=1);

namespace Modules\Holocron\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Holocron\User\Database\Factories\UserSettingFactory;

/**
 * @property-read float $weight
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
}
