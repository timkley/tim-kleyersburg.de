<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire;

use Livewire\Attributes\Validate;
use Modules\Holocron\Quest\Actions\AddQuestLink;
use Modules\Holocron\Quest\Actions\DeleteQuestLink;

trait WithLinks
{
    #[Validate('required')]
    #[Validate('url')]
    public string $linkDraft = '';

    public function addLink(): void
    {
        $this->validateOnly('linkDraft');

        (new AddQuestLink)->handle($this->quest, ['url' => $this->linkDraft]);

        $this->reset(['linkDraft']);
    }

    public function deleteLink(int $pivotId): void
    {
        (new DeleteQuestLink)->handle($this->quest, $pivotId);
    }
}
