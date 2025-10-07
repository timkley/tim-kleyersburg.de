<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire;

use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Printer\Services\Printer;
use Modules\Holocron\Quest\Models\Quest;

#[Title('Quests')]
class Show extends HolocronComponent
{
    use WithAI;
    use WithFileUploads;
    use WithLinks;
    use WithNotes;
    use WithRecurrence;
    use WithReminders;

    public Quest $quest;

    #[Validate('required')]
    #[Validate('min:3')]
    #[Validate('max:255')]
    public string $name = '';

    public ?string $date = '';

    public ?string $description = '';

    /** @var ?UploadedFile */
    #[Validate('image')]
    public $image;

    #[Url]
    public bool $showAllSubquests = false;

    #[Validate('required')]
    #[Validate('min:3')]
    #[Validate('max:255')]
    public string $questDraft = '';

    /** @var string[] */
    public array $subquestSuggestions = [];

    public function updating(string $property, mixed $value): void
    {
        if (! in_array($property, ['name', 'description', 'date'])) {
            return;
        }

        $this->validateOnly($property);

        $this->quest->update([
            $property => $value,
        ]);

        $this->reset($property);
    }

    public function updatedImage(): void
    {
        /** @var Collection<int, string> $images */
        $images = $this->quest->images;

        $storedPath = $this->image->store('quests', 'public');
        if (! $storedPath) {
            return;
        }

        $images = $images->push($storedPath);

        $this->quest->update([
            'images' => $images,
        ]);

        $this->reset('image');
    }

    public function toggleComplete(): void
    {
        if ($this->quest->isCompleted()) {
            $this->quest->update(['completed_at' => null]);
        } else {
            $this->quest->complete();
        }
    }

    public function toggleIsNote(): void
    {
        $this->quest->update([
            'is_note' => ! $this->quest->is_note,
        ]);
    }

    public function move(?int $id): void
    {
        if (is_null($id)) {
            return;
        }

        $this->quest->update([
            'quest_id' => $id,
        ]);

        Flux::modal('parent-search')->close();
    }

    public function print(): void
    {
        $this->quest->update([
            'should_be_printed' => true,
        ]);

        Printer::print('holocron-quest::print-view', ['quest' => $this->quest], [
            route('holocron.quests.complete', [$this->quest]),
        ]);
    }

    public function addQuest(?string $name = null): void
    {
        if (is_null($name)) {
            $this->validateOnly('questDraft');
        }

        Quest::create([
            'quest_id' => $this->quest->id,
            'name' => $name ?? $this->questDraft,
        ]);

        $this->reset(['questDraft']);
    }

    public function deleteQuest(int $id): void
    {
        Quest::destroy($id);
    }

    public function mount(Quest $quest): void
    {
        $this->quest = $quest;
        $this->date = $quest->date?->format('Y-m-d') ?? null;
        $this->name = $quest->name;
        $this->description = $quest->description;
    }

    public function render(): View
    {
        $query = Quest::query();

        if ($this->quest->daily) {
            $query->where(function (Builder $query) {
                $query->where('quest_id', $this->quest->id)
                    ->orWhereDate('date', '<=', $this->quest->date);
            })->where('daily', false);
        } else {
            $query->where('quest_id', $this->quest->id);
        }

        if (! $this->showAllSubquests) {
            $query->notCompleted();
        }

        $questChildren = $query
            ->get();

        return view('holocron-quest::show', [
            'questChildren' => $questChildren,
        ])->title($this->quest->name);
    }
}
