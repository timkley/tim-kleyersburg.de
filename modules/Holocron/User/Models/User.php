<?php

declare(strict_types=1);

namespace Modules\Holocron\User\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Holocron\User\Database\Factories\UserFactory;
use Modules\Holocron\User\Enums\ExperienceType;

/**
 * @property-read string $email
 * @property-read ?UserSetting $settings
 * @property-read int $experience
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected int $baseXp = 100;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public static function tim(): self
    {
        return self::query()
            ->where('email', 'timkley@gmail.com')
            ->sole();
    }

    public function isTim(): bool
    {
        return $this->email === 'timkley@gmail.com';
    }

    public function addExperience(int $amount, ExperienceType $type, int $identifier): void
    {
        if ($this->experienceLogs()->where('type', $type)->where('identifier', $identifier)->count()) {
            return;
        }

        $currentXp = $this->experience;

        $this->experienceLogs()->create([
            'amount' => $amount,
            'type' => $type,
            'identifier' => $identifier,
        ]);

        $adjustment = max(0, $currentXp + $amount);

        $this->update([
            'experience' => $adjustment,
        ]);
    }

    /**
     * @return HasMany<ExperienceLog, $this>
     */
    public function experienceLogs(): HasMany
    {
        return $this->hasMany(ExperienceLog::class);
    }

    /**
     * @return HasOne<UserSetting, $this>
     */
    public function settings(): HasOne
    {
        return $this->hasOne(UserSetting::class);
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return Attribute<int, never>
     */
    protected function level(): Attribute
    {
        return Attribute::make(
            get: fn (): int => max(1, (int) floor(sqrt($this->experience / $this->baseXp))),
        );
    }

    /**
     * @return Attribute<int, never>
     */
    protected function xpForNextLevel(): Attribute
    {
        $currentLevel = $this->level;
        $nextLevel = $currentLevel + 1;

        return Attribute::make(
            get: fn (): int => $this->baseXp * pow($nextLevel, 2),
        );
    }
}
