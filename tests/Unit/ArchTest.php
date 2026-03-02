<?php

declare(strict_types=1);

arch('globals')
    ->expect(['dd', 'dump', 'ray'])
    ->not->toBeUsed();

arch()
    ->expect('App\Models')
    ->toBeClasses()
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->ignoring(App\Models\Article::class);

arch()->preset()->php()->ignoring(App\Ai\Tools\EvalTool::class);
arch()->preset()->security()->ignoring('Database\Factories')->ignoring(App\Ai\Tools\EvalTool::class);
