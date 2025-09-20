<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;
use Modules\Holocron\Quest\Models\Quest;

class PrintQueueController
{
    public function __invoke(): JsonResponse
    {
        $lock = cache()->lock('print-queue', 60);

        if ($lock->get()) {
            try {
                $tasksToPrint = Quest::query()
                    ->where('should_be_printed', true)
                    ->whereNull('printed_at')
                    ->get();

                if ($tasksToPrint->isEmpty()) {
                    return response()->json();
                }

                Quest::query()->whereIn('id', $tasksToPrint->pluck('id'))->update(['printed_at' => now()]);
                Quest::query()->whereIn('id', $tasksToPrint->pluck('id'))->where('delete_after_print', true)->delete();

                return response()->json($tasksToPrint->map(function (Quest $quest) {
                    return [
                        'name' => $quest->name,
                        'breadcrumb' => $quest->breadcrumb()->pluck('name')->join(' > '),
                        'scan_url' => URL::signedRoute('holocron.quests.complete', ['id' => $quest->id]),
                    ];
                }));
            } finally {
                $lock->release();
            }
        }

        return response()->json();
    }
}
