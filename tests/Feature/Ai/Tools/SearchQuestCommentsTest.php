<?php

declare(strict_types=1);

use App\Ai\Tools\SearchQuestComments;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Models\Note;
use Modules\Holocron\Quest\Models\Quest;

it('returns no-results message when no notes match', function () {
    $tool = new SearchQuestComments;
    $result = $tool->handle(new Request(['query' => 'nonexistent']));

    expect($result)->toBe('No notes found matching the query.');
});

it('formats matching notes with quest context', function () {
    $quest = Quest::factory()->create(['name' => 'Test Quest']);
    Note::factory()->for($quest)->create(['content' => 'Important finding', 'role' => 'user']);

    $tool = new SearchQuestComments;
    $result = $tool->handle(new Request(['query' => 'finding']));

    expect($result)->toContain('Test Quest')
        ->toContain('Important finding');
});
