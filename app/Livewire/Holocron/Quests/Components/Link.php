<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Quests\Components;

use App\Models\Webpage;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class Link extends Component
{
    public string $url = '';

    public string $title = '';

    public int $questId;

    public int $webpageId;

    public function updatedTitle(string $value): void
    {
        DB::table('quest_webpage')
            ->where('quest_id', $this->questId)
            ->where('webpage_id', $this->webpageId)
            ->update(['title' => $value]);
    }

    public function mount(Webpage $webpage): void
    {
        $this->url = $webpage->url;
        $this->title = $webpage->pivot->title ?? $webpage->title ?? $webpage->url;
        $this->questId = $webpage->pivot->quest_id;
        $this->webpageId = $webpage->pivot->webpage_id;
    }

    public function render(): View
    {
        return view('holocron.quests.components.link');
    }
}
