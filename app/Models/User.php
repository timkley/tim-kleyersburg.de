<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property ?UserSetting $settings
 * @property-read int $experience
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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

    public function isTim(): bool
    {
        return $this->email === 'timkley@gmail.com';
    }

    public function addExperience(int $amount, string $type, string $identifier, string $description): void
    {
        if ($this->experienceLogs()->where('identifier', $identifier)->count()) {
            return;
        }

        $currentXp = $this->experience;

        $this->experienceLogs()->create([
            'amount' => $amount,
            'type' => $type,
            'identifier' => $identifier,
            'description' => $description,
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
}
