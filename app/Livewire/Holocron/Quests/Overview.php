<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Quests;

use App\Enums\Holocron\QuestStatus;
use App\Jobs\CrawlWebpageInformation;
use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Quest;
use App\Models\Holocron\QuestNote;
use App\Models\Webpage;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;

#[Title('Quests')]
class Overview extends HolocronComponent
{
    use WithFileUploads;

    public ?Quest $quest = null;

    #[Validate('required')]
    #[Validate('min:3')]
    #[Validate('max:255')]
    public ?string $name = '';

    public ?string $description = '';

    public QuestStatus $status;

    #[Validate('image')]
    public $image;

    #[Validate('required')]
    #[Validate('url')]
    public string $linkDraft = '';

    #[Validate('required')]
    #[Validate('min:3')]
    #[Validate('max:255')]
    public string $questDraft = '';

    #[Validate('required')]
    #[Validate('min:3')]
    public string $noteDraft = '';

    public function updating($property, $value): void
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
        $images = $this->quest->images ?? collect();
        $images = $images->push($this->image->store('quests', 'public'));

        $this->quest->update([
            'images' => $images,
        ]);

        $this->reset('image');
    }

    public function addQuest(): void
    {
        $this->validateOnly('questDraft');

        Quest::create([
            'quest_id' => $this->quest?->id,
            'name' => $this->questDraft,
        ]);

        $this->reset(['questDraft']);
    }

    public function deleteQuest(int $id): void
    {
        Quest::destroy($id);
    }

    public function addLink(): void
    {
        $this->validateOnly('linkDraft');

        $webpage = Webpage::createOrFirst([
            'url' => $this->linkDraft,
        ]);

        if ($webpage->wasRecentlyCreated) {
            new CrawlWebpageInformation($webpage)->handle();
        }

        $this->quest->webpages()->attach($webpage);
    }

    public function addNote(): void
    {
        $this->validateOnly('noteDraft');

        $this->quest->notes()->create([
            'content' => $this->noteDraft,
        ]);

        $this->reset(['noteDraft']);
    }

    public function deleteNote(int $id): void
    {
        QuestNote::destroy($id);
    }

    public function mount(?Quest $quest): void
    {
        $this->quest = $quest ?? new Quest;
        $this->name = $quest->name;
        $this->description = $quest->description;
        $this->status = $quest->status ?? QuestStatus::Open;
    }

    public function render(): View
    {
        return view('holocron.quests.overview');
    }

    public function rendering(View $view): void
    {
        $view->title($this->quest->name ?? 'Quests');
    }
}
