<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Livewire\Intents;

use Livewire\Wireable;

class Intent implements Wireable
{
    public function __construct(public string $type, public string $label, public string $property, public mixed $value) {}

    /**
     * @param  array<string,mixed>  $value
     */
    public static function fromLivewire($value): self
    {
        return new self($value['type'], $value['label'], $value['property'], $value['value']);
    }

    /**
     * @return array<string, mixed>
     */
    public function toLivewire(): array
    {
        return [
            'type' => $this->type,
            'label' => $this->label,
            'property' => $this->property,
            'value' => $this->value,
        ];
    }
}
