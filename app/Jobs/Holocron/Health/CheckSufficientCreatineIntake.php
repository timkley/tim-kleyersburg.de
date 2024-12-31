<?php

declare(strict_types=1);

namespace App\Jobs\Holocron\Health;

use App\Enums\Holocron\Health\IntakeTypes;
use App\Models\Holocron\Health\Intake;
use App\Notifications\DiscordTimChannel;
use App\Notifications\Holocron\Health\InsufficientCreatineIntake;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckSufficientCreatineIntake implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        if (Intake::where('type', IntakeTypes::Creatine)->whereDate('created_at', now())->count() === 0) {
            (new DiscordTimChannel())->notify(new InsufficientCreatineIntake());
        }
    }
}
