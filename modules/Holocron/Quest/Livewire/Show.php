<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire;

use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Quest\Actions\AddQuestAttachment;
use Modules\Holocron\Quest\Actions\CreateQuest as CreateQuestAction;
use Modules\Holocron\Quest\Actions\DeleteQuest as DeleteQuestAction;
use Modules\Holocron\Quest\Actions\MoveQuest;
use Modules\Holocron\Quest\Actions\PrintQuest;
use Modules\Holocron\Quest\Actions\RemoveQuestAttachment;
use Modules\Holocron\Quest\Actions\ToggleQuestComplete;
use Modules\Holocron\Quest\Actions\UpdateQuest;
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

    /** @var UploadedFile[] */
    #[Validate('nullable|array')]
    public array $newAttachments = [];

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

        (new UpdateQuest)->handle($this->quest, [$property => $value]);

        $this->reset($property);
    }

    public function updatedNewAttachments(): void
    {
        if (! $this->newAttachments) {
            return;
        }

        foreach ($this->newAttachments as $attachment) {
            (new AddQuestAttachment)->handle($this->quest, $attachment);
        }

        $this->quest->refresh();
        $this->reset('newAttachments');
    }

    public function removeAttachment(string $path): void
    {
        (new RemoveQuestAttachment)->handle($this->quest, $path);
    }

    public function toggleComplete(): void
    {
        (new ToggleQuestComplete)->handle($this->quest);
    }

    public function toggleIsNote(): void
    {
        (new UpdateQuest)->handle($this->quest, ['is_note' => ! $this->quest->is_note]);
    }

    public function move(?int $id): void
    {
        if (is_null($id)) {
            return;
        }

        (new MoveQuest)->handle($this->quest, ['quest_id' => $id]);

        Flux::modal('parent-search')->close();
    }

    public function print(): void
    {
        (new PrintQuest)->handle($this->quest);
    }

    public function addQuest(?string $name = null): void
    {
        if (is_null($name)) {
            $this->validateOnly('questDraft');
        }

        (new CreateQuestAction)->handle([
            'quest_id' => $this->quest->id,
            'name' => $name ?? $this->questDraft,
        ]);

        $this->reset(['questDraft']);
    }

    public function deleteQuest(int $id): void
    {
        (new DeleteQuestAction)->handle(Quest::findOrFail($id));
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
