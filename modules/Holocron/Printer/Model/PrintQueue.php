<?php

declare(strict_types=1);

namespace Modules\Holocron\Printer\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Modules\Holocron\Printer\Database\Factories\PrintQueueFactory;

/**
 * @property int $id
 * @property string $image
 * @property array $actions
 * @property string|null $printable_type
 * @property int|null $printable_id
 * @property Carbon|null $printed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class PrintQueue extends Model
{
    /** @use HasFactory<PrintQueueFactory> */
    use HasFactory;

    /**
     * @return MorphTo<Model, $this>
     */
    public function printable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Returns the length in mm
     */
    public function length(): int
    {
        $topFeed = 16;

        $image = Storage::disk('public')->get($this->image);

        if (is_null($image)) {
            return 0;
        }

        $imageResource = imagecreatefromstring($image);
        $imageHeightInPixels = imagesy($imageResource);
        $imageHeightInMm = (int) round($imageHeightInPixels / 7.25);

        $actionsInMm = count($this->actions) * 41;

        $bottomFeed = 10;

        return $topFeed + $imageHeightInMm + $actionsInMm + $bottomFeed;
    }

    protected static function newFactory(): PrintQueueFactory
    {
        return PrintQueueFactory::new();
    }

    protected function casts(): array
    {
        return [
            'actions' => 'array',
            'printed_at' => 'datetime',
        ];
    }
}
