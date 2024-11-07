<?php

namespace App\Data;

use BenBjurstrom\Prezet\Data\FrontmatterData;
use WendellAdriel\ValidatedDTO\Attributes\Rules;

class CustomFrontmatterData extends FrontmatterData
{
    #[Rules(['required', 'string'])]
    public string $slug;

    #[Rules(['nullable', 'string'])]
    public string $excerpt;

    #[Rules(['nullable', 'bool'])]
    public ?bool $rumbling;
}
