<?php

declare(strict_types=1);

namespace Modules\Holocron\Gear\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Modules\Holocron\Gear\Database\Factories\CategoryFactory;

/**
 * @property int $id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $table = 'gear_categories';

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }
}
