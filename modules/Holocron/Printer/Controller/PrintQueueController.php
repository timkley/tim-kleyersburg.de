<?php

declare(strict_types=1);

namespace Modules\Holocron\Printer\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Modules\Holocron\Printer\Model\PrintQueue;
use Modules\Holocron\User\Models\User;

class PrintQueueController
{
    public function __invoke(): JsonResponse
    {
        $timeout = config('printer.long_poll.timeout');
        $checkInterval = config('printer.long_poll.check_interval');
        $startTime = time();

        $lock = cache()->lock(
            config('printer.cache_lock.key'),
            config('printer.cache_lock.timeout')
        );

        if (! $lock->get()) {
            return response()->json([]);
        }

        try {
            if (User::tim()->settings->printer_silenced) {
                return response()->json([]);
            }

            while (true) {
                $items = $this->checkForItems();

                if (! empty($items)) {
                    $this->markAsPrinted(array_column($items, 'id'));

                    return response()->json($items);
                }

                $elapsed = time() - $startTime;

                if ($elapsed >= $timeout) {
                    return response()->json([]);
                }

                sleep($checkInterval);
            }
        } finally {
            $lock->release();
        }
    }

    /**
     * Check for unprinted items in the queue
     *
     * @return array<int, array{id: int, image: string, actions: array<mixed>, created_at: string}>
     */
    private function checkForItems(): array
    {
        $itemsToPrint = PrintQueue::query()
            ->whereNull('printed_at')
            ->orderBy('created_at')
            ->get();

        if ($itemsToPrint->isEmpty()) {
            return [];
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

        return $items;
    }

    /**
     * Mark items as printed
     *
     * @param  array<int>  $ids
     */
    private function markAsPrinted(array $ids): void
    {
        PrintQueue::query()
            ->whereIn('id', $ids)
            ->update(['printed_at' => now()]);
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
