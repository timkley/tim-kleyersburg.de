<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Quests;

use App\Enums\Holocron\QuestStatus;
use App\Jobs\CrawlWebpageInformation;
use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Quest;
use App\Models\Holocron\QuestNote;
use App\Models\Webpage;
use Denk\Facades\Denk;
use Denk\ValueObjects\DeveloperMessage;
use Denk\ValueObjects\UserMessage;
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
    use WithFileUploads;

    public Quest $quest;

    #[Validate('required')]
    #[Validate('min:3')]
    #[Validate('max:255')]
    public string $name = '';

    public string $parentSearchTerm = '';

    /** @var array<mixed>|Collection<int, Quest> */
    public $possibleParents = [];

    public ?string $description = '';

    public QuestStatus $status;

    /** @var ?UploadedFile */
    #[Validate('image')]
    public $image;

    #[Validate('required')]
    #[Validate('url')]
    public string $linkDraft = '';

    #[Url]
    public bool $showAllSubquests = false;

    #[Validate('required')]
    #[Validate('min:3')]
    #[Validate('max:255')]
    public string $questDraft = '';

    /** @var string[] */
    public array $subquestSuggestions = [];

    #[Validate('required')]
    #[Validate('min:3')]
    public string $noteDraft = '';

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

    public function updatingParentSearchTerm(mixed $value): void
    {
        if (empty($value)) {
            $this->possibleParents = [];

            return;
        }

        $this->possibleParents = Quest::query()
            ->where('name', 'like', '%'.$value.'%')
            ->limit(20)
            ->get();
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

        Flux::modal('move')->close();
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

    public function generateSolution(): void
    {
        $prompt = <<<'EOT'
Aufgabenstruktur:
---

EOT;

        foreach ($this->quest->breadcrumb() as $index => $quest) {
            $indent = str_repeat('  ', $index);

            $prompt .= <<<EOT
{$indent}- Name: {$quest->name}
{$indent}  Beschreibung: {$quest->description}

EOT;
        }

        $prompt .= '---';

        $solution = Denk::text()
            ->model('google/gemini-2.5-flash-preview-05-20:online')
            ->messages([
                new DeveloperMessage(view('prompts.solution')->render()),
                new UserMessage($prompt),
            ])
            ->generate();

        $this->quest->notes()->create([
            'content' => str($solution)->markdown(),
        ]);
    }

    public function generateSubquests(): void
    {
        $children = $this->quest->children->implode('name', '\n');

        $this->subquestSuggestions = Denk::json()
            ->model('google/gemini-flash-1.5-8b')
            ->properties([
                'subtasks' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => [
                                'type' => 'string',
                                'description' => 'The name of the subtask',
                            ],
                        ],
                    ],
                ],
            ])
            ->messages([
                new UserMessage(view('prompts.subquests', [
                    'name' => $this->name,
                    'description' => $this->description,
                    'children' => $children,
                ])->render()),
            ])->generate()['subtasks'];
    }

    public function addLink(): void
    {
        $this->validateOnly('linkDraft');

        $webpage = Webpage::createOrFirst([
            'url' => $this->linkDraft,
        ]);

        if ($webpage->wasRecentlyCreated) {
            CrawlWebpageInformation::dispatch($webpage);
        }

        $this->quest->webpages()->attach($webpage);

        $this->reset(['linkDraft']);
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
