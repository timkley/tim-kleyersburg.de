<?php

declare(strict_types=1);

use Modules\Holocron\User\Models\User;

it('resolves tim and identifies tim user', function () {
    $tim = User::factory()->create(['email' => 'timkley@gmail.com']);
    $other = User::factory()->create();

    expect(User::tim()->is($tim))->toBeTrue();
    expect($tim->isTim())->toBeTrue();
    expect($other->isTim())->toBeFalse();
});
