<?php

declare(strict_types=1);

use App\Livewire\Articles\Show;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

use function Pest\Laravel\get;

it('shows the article overview', function () {
    get('/articles')
        ->assertSuccessful();
});

it('updates the prezet index in local environment', function () {
    $mock = Mockery::mock(Prezet\Prezet\Actions\UpdateIndex::class);
    $mock->shouldReceive('handle')->once();
    app()->instance(Prezet\Prezet\Actions\UpdateIndex::class, $mock);

    app()->detectEnvironment(fn () => 'local');

    get('/articles')
        ->assertSuccessful();
});

it('shows an article', function () {
    Http::fake([
        'https://api.torchlight.dev/highlight' => Http::response('ok'),
    ]);
    get('/articles/resize-images-with-mogrify')
        ->assertSuccessful()
        ->assertSee('Use ImageMagicks mogrify CLI to batch resize images');
});

it('shows the ramble button on articles that support rambling', function () {
    Http::fake([
        'https://api.torchlight.dev/highlight' => Http::response('ok'),
    ]);

    get('/articles/keys-to-effiency/part-1-hard-skills')
        ->assertSuccessful()
        ->assertSee('Turn f-bomb filter');
});

it('toggles the rambling state when calling ramble', function () {
    Http::fake([
        'https://api.torchlight.dev/highlight' => Http::response('ok'),
    ]);

    $articleId = 5; // keys-to-effiency/part-1-hard-skills
    cache()->store('file_persistent')->forever("ramble.{$articleId}", '# Cached ramble content');

    $component = Livewire::test(Show::class, ['slug' => 'keys-to-effiency/part-1-hard-skills']);

    expect($component->get('rambling'))->toBeFalse();

    $component->call('ramble');

    expect($component->get('rambling'))->toBeTrue();

    $component->call('ramble');

    expect($component->get('rambling'))->toBeFalse();

    cache()->store('file_persistent')->forget("ramble.{$articleId}");
});

it('renders rambled content from cache when rambling is enabled', function () {
    Http::fake([
        'https://api.torchlight.dev/highlight' => Http::response('ok'),
    ]);

    $articleId = 5; // keys-to-effiency/part-1-hard-skills
    $rambledContent = '# Rambled Test Content';

    cache()->store('file_persistent')->forever("ramble.{$articleId}", $rambledContent);

    Livewire::test(Show::class, ['slug' => 'keys-to-effiency/part-1-hard-skills'])
        ->call('ramble')
        ->assertSee('Rambled Test Content');

    cache()->store('file_persistent')->forget("ramble.{$articleId}");
});

it('makes sure the feed is working', function () {
    Http::fake([
        'https://api.torchlight.dev/highlight' => Http::response('ok'),
    ]);
    get('/feed.xml')
        ->assertSuccessful();
});
