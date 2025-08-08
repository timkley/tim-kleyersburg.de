<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Gear;

use App\Enums\Holocron\Gear\Property;
use App\Models\Holocron\Gear\Item;
use App\Models\Holocron\Gear\Journey;
use App\Models\Holocron\Gear\JourneyItem;

trait WithPacklistGeneration
{
    public function generatePacklist(Journey $journey): void
    {
        $journey->journeyItems()->delete();

        $packingList = [];

        Item::all()->each(function (Item $item) use ($journey, &$packingList) {
            if ($this->itemShouldBeIncluded($item, $journey)) {
                $packingList[] = [
                    'journey_id' => $journey->id,
                    'item_id' => $item->id,
                    'quantity' => $this->calculateQuantity($item, $journey),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        });

        if (! empty($packingList)) {
            JourneyItem::insert($packingList);
        }
    }

    protected function itemShouldBeIncluded(Item $item, Journey $journey): bool
    {
        if (empty($item->properties)) {
            return true;
        }

        /** @var Property $property */
        foreach ($item->properties as $property) {
            if (! $property->meetsCondition($journey)) {
                return false;
            }
        }

        return true;
    }

    protected function calculateQuantity(Item $item, Journey $journey): int
    {
        if ($item->quantity) {
            return $item->quantity;
        }

        return (int) max(ceil($item->quantity_per_day * $journey->days), 1);
    }
}
