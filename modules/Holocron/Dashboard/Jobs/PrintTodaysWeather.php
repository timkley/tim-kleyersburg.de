<?php

declare(strict_types=1);

namespace Modules\Holocron\Dashboard\Jobs;

use App\Services\Weather;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Modules\Holocron\Printer\Services\Printer;

class PrintTodaysWeather implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $todaysForecast = Weather::forecast('Fellbach', today(), today())->days['0'];
        Printer::print('holocron-dashboard::print-weather', ['forecast' => $todaysForecast]);
    }
}
