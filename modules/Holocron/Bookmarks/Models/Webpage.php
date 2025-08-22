<?php

declare(strict_types=1);

namespace Modules\Holocron\Bookmarks\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Holocron\Bookmarks\Database\Factories\WebpageFactory;

/**
 * @property-read string $title
 * @property-read string $description
 * @property-read string $summary
 * @property-read string $url
 * @property-read mixed $pivot
 * @property-read CarbonImmutable $created_at
 */
class Webpage extends Model
{
    /** @use HasFactory<WebpageFactory> */
    use HasFactory;

    protected static function newFactory(): WebpageFactory
    {
        return WebpageFactory::new();
    }
}
