<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Quests;

use App\Enums\Holocron\QuestStatus;
use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Quest\Quest;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;

#[Title('Quests')]
class Show extends HolocronComponent
{
    use WithAI;
    use WithFileUploads;
    use WithLinks;
    use WithNotes;
    use WithReminders;

    public Quest $quest;

    #[Validate('required')]
    #[Validate('min:3')]
    #[Validate('max:255')]
    public string $name = '';

    public ?string $description = '';

    public QuestStatus $status;

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
        if (! in_array($property, ['name', 'description', 'status'])) {
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

    public function setStatus(string $status): void
    {
        $this->quest->setStatus(QuestStatus::from($status));
    }

    public function move(?int $id): void
    {
        $this->quest->update([
            'quest_id' => $id,
        ]);

        Flux::modal('parent-search')->close();
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
        $this->name = $quest->name;
        $this->description = $quest->description;
        $this->status = $quest->status;
    }

    public function render(): View
    {
        $questChildren = $this->quest
            ->children()
            ->when(! $this->showAllSubquests, function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->where('status', '!=', QuestStatus::Complete)
                        ->orWhere(function (Builder $subQuery) {
                            $subQuery->where('status', QuestStatus::Complete)
                                ->where('updated_at', '>', now()->subWeeks(2));
                        });
                });
            })
            ->orderByDesc('status')
            ->orderByDesc('updated_at')
            ->get();

        return view('holocron.quests.show', [
            'questChildren' => $questChildren,
        ])->title($this->quest->name);
    }
}
