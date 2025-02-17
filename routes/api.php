<?php

declare(strict_types=1);

Route::post('/store-digest', function () {
    cache()->put('daily-digest', request()->json('body'), now()->endOfDay());

    return response()->json(['message' => 'Digest stored']);
})->name('store-digest');
