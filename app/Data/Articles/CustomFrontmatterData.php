<?php

declare(strict_types=1);

namespace App\Data\Articles;

use Prezet\Prezet\Data\FrontmatterData;
use WendellAdriel\ValidatedDTO\Attributes\Rules;

class CustomFrontmatterData extends FrontmatterData
{
    #[Rules(['nullable', 'string'])]
    public string $excerpt;

    #[Rules(['nullable', 'bool'])]
    public ?bool $rambling = null;
}
