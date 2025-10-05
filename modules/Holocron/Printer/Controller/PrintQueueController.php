<?php

declare(strict_types=1);

namespace Modules\Holocron\Printer\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Modules\Holocron\Printer\Model\PrintQueue;

class PrintQueueController
{
    public function __invoke(): JsonResponse
    {
        $lock = cache()->lock('print-queue', 60);

        if ($lock->get()) {
            try {
                $itemsToPrint = PrintQueue::query()
                    ->whereNull('printed_at')
                    ->orderBy('created_at')
                    ->get();

                if ($itemsToPrint->isEmpty()) {
                    return response()->json();
                }

                $items = [];

                foreach ($itemsToPrint as $item) {
                    $imageUrl = $this->getImageUrl($item->image);

                    if ($imageUrl !== null) {
                        $items[] = [
                            'id' => $item->id,
                            'image' => $imageUrl,
                            'actions' => $item->actions,
                            'created_at' => $item->created_at->toISOString(),
                        ];
                    }
                }

                // Mark items as printed
                PrintQueue::query()
                    ->whereIn('id', $itemsToPrint->pluck('id'))
                    ->update(['printed_at' => now()]);

                return response()->json($items);
            } finally {
                $lock->release();
            }
        }

        return response()->json();
    }

    /**
     * Get absolute image URL
     */
    private function getImageUrl(string $imagePath): ?string
    {
        if (! Storage::disk('public')->exists($imagePath)) {
            return null;
        }

        // Generate absolute URL
        $relativeUrl = Storage::disk('public')->url($imagePath);

        return url($relativeUrl);
    }
}
