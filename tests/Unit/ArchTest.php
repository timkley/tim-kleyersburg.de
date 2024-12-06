<?php

declare(strict_types=1);

arch('globals')
    ->expect(['dd', 'dump', 'ray'])
    ->not->toBeUsed();

arch()
    ->expect('App\Livewire')
    ->toBeClasses()
    ->toExtend('Livewire\Component');

arch()
    ->expect('App\Models')
    ->toBeClasses()
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->ignoring('App\Models\Article');

arch()->preset()->php();
arch()->preset()->security();
