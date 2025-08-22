<?php

declare(strict_types=1);

use App\Livewire\Articles\Index;
use App\Livewire\Articles\Show;
use App\Livewire\Pages\Einmaleins;
use App\Livewire\Pages\Home;
use Illuminate\Support\Facades\Route;
use Prezet\Prezet\Models\Document;

Route::get('/', Home::class)->name('pages.home');

Route::get('/articles', Index::class)->name('articles.index');

Route::get('/articles/img/{path}', function ($path) {
    $file = Prezet\Prezet\Prezet::getImage($path);
    $size = mb_strlen($file);

    return response($file, 200, [
        'Content-Type' => match (pathinfo($path, PATHINFO_EXTENSION)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'image/webp'
        },
        'Content-Length' => $size,
        'Accept-Ranges' => 'bytes',
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})
    ->name('prezet.image')
    ->where('path', '.*');

Route::get('/articles/ogimage/{slug}', function ($slug) {
    $doc = app(Document::class)::query()
        ->where('slug', $slug)
        ->when(config('app.env') !== 'local', function ($query) {
            return $query->where('draft', false);
        })
        ->firstOrFail();

    return view('components.ogimage', [
        'fm' => $doc->frontmatter,
    ]);
})
    ->name('prezet.ogimage')
    ->where('slug', '.*');

Route::get('/articles/{slug}', Show::class)
    ->where('slug', '.*')
    ->name('prezet.show');

Route::get('/einmaleins', Einmaleins::class)->name('pages.einmaleins');

Route::feeds();
