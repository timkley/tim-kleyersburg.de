<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WebpageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $url
 * @property mixed $pivot
 */
class Webpage extends Model
{
    /** @use HasFactory<WebpageFactory> */
    use HasFactory;
}
