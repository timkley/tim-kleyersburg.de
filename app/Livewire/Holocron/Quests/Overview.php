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
use Denk\ValueObjects\UserMessage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
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

    /** @var ?UploadedFile */
    #[Validate('image')]
    public $image;

    #[Validate('required')]
    #[Validate('url')]
    public string $linkDraft = '';

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

    public function addQuest(?string $name = null): void
    {
        if (is_null($name)) {
            $this->validateOnly('questDraft');
        }

        Quest::create([
            'quest_id' => $this->quest?->id,
            'name' => $name ?? $this->questDraft,
        ]);

        $this->reset(['questDraft']);
    }

    public function deleteQuest(int $id): void
    {
        Quest::destroy($id);
    }

    public function generateSubquests(): void
    {
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
                new UserMessage(
                    <<<EOT
Du bist ein Assistent zur Aufgabenzerlegung. Deine Aufgabe ist es, eine Hauptaufgabe in die nächsten logischen und umsetzbaren Unteraufgaben zu zerlegen.

**Hauptaufgabe:**
$this->name
$this->description

**Anweisungen:**

1.  **Analysiere die Hauptaufgabe:** Identifiziere die *ersten* Schritte, die zur Bearbeitung notwendig sind.
2.  **Generiere Unteraufgaben:** Erstelle eine Liste von maximal 3-5 Unteraufgaben.
3.  **Reduziere Komplexität:** Jede Unteraufgabe muss *signifikant* einfacher sein als die Hauptaufgabe.
4.  **Formulierung:** Schreibe klare, prägnante und handlungsorientierte Unteraufgaben (z.B. "Konzept erstellen", "Daten sammeln", "Kunden kontaktieren").
5.  **Kontexthandhabung:** Wenn der Kontext der Hauptaufgabe unklar ist, schlage allgemeine erste Schritte vor, die typischerweise für eine solche Aufgabe anfallen, oder formuliere eine Unteraufgabe zur Klärung (z.B. "Anforderungen für [Thema] definieren").

**Output:**
Gib *nur* die Liste der generierten Unteraufgaben als JSON-Array zurück, eine pro Zeile, ohne zusätzliche Erklärungen oder Nummerierungen.
EOT
                ),
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

    public function mount(?Quest $quest): void
    {
        $this->quest = $quest ?? new Quest;
        $this->name = $quest->name;
        $this->description = $quest->description;
        $this->status = $quest->status ?? QuestStatus::Open;
    }

    public function render(): View
    {
        return view('holocron.quests.overview')
            ->title($this->quest->name ?? 'Quests');
    }
}
