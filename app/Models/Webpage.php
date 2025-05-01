<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $url
 */
class Webpage extends Model
{
    /** @use HasFactory<\Database\Factories\WebpageFactory> */
    use HasFactory;

}
