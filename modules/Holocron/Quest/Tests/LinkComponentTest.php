<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Modules\Holocron\Bookmarks\Models\Webpage;
use Modules\Holocron\Quest\Livewire\Components\Link;
use Modules\Holocron\Quest\Models\Quest;

it('renders a link component', function () {
    $quest = Quest::factory()->create();
    $webpage = Webpage::factory()->create(['url' => 'https://example.com']);
    $quest->webpages()->attach($webpage, ['title' => 'Example Link']);

    $loadedWebpage = $quest->webpages()->first();

    Livewire::test(Link::class, ['webpage' => $loadedWebpage])
        ->assertStatus(200)
        ->assertSee('Example Link')
        ->assertSet('url', 'https://example.com');
});

it('uses webpage title as fallback when pivot title is null', function () {
    $quest = Quest::factory()->create();
    $webpage = Webpage::factory()->create([
        'url' => 'https://example.com',
        'title' => 'Webpage Title',
    ]);
    $quest->webpages()->attach($webpage, ['title' => null]);

    $loadedWebpage = $quest->webpages()->first();

    Livewire::test(Link::class, ['webpage' => $loadedWebpage])
        ->assertSet('title', 'Webpage Title');
});

it('uses url as fallback when both titles are null', function () {
    $quest = Quest::factory()->create();
    $webpage = Webpage::factory()->create([
        'url' => 'https://example.com',
        'title' => null,
    ]);
    $quest->webpages()->attach($webpage, ['title' => null]);

    $loadedWebpage = $quest->webpages()->first();

    Livewire::test(Link::class, ['webpage' => $loadedWebpage])
        ->assertSet('title', 'https://example.com');
});

it('updates the title in the pivot table', function () {
    $quest = Quest::factory()->create();
    $webpage = Webpage::factory()->create();
    $quest->webpages()->attach($webpage, ['title' => 'Old Title']);

    $loadedWebpage = $quest->webpages()->first();

    Livewire::test(Link::class, ['webpage' => $loadedWebpage])
        ->set('title', 'New Title');

    $pivotTitle = DB::table('quest_webpage')
        ->where('quest_id', $quest->id)
        ->where('webpage_id', $webpage->id)
        ->value('title');

    expect($pivotTitle)->toBe('New Title');
});
