<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\_Shared\Livewire\PrinterStatus;
use Modules\Holocron\User\Models\User;
use Modules\Holocron\User\Models\UserSetting;

beforeEach(function () {
    $this->user = User::factory()->create();
    UserSetting::factory()->create([
        'user_id' => $this->user->id,
        'printer_silenced' => false,
    ]);
    $this->actingAs($this->user);
});

it('renders printer status component', function () {
    Livewire::test(PrinterStatus::class)
        ->assertOk()
        ->assertSee('Drucker aktiv');
});

it('displays active printer status when not silenced', function () {
    Livewire::test(PrinterStatus::class)
        ->assertSee('Drucker aktiv')
        ->assertDontSee('Drucker stumm');
});

it('displays silenced printer status when silenced', function () {
    $this->user->settings()->update(['printer_silenced' => true]);
    $this->user->settings->refresh();

    Livewire::test(PrinterStatus::class)
        ->assertSee('Drucker stumm')
        ->assertDontSee('Drucker aktiv');
});

it('toggles printer status from active to silenced', function () {
    expect($this->user->settings->printer_silenced)->toBe(0);

    Livewire::test(PrinterStatus::class)
        ->call('toggle')
        ->assertSee('Drucker stumm');

    expect($this->user->settings->fresh()->printer_silenced)->toBe(1);
});

it('toggles printer status from silenced to active', function () {
    $this->user->settings()->update(['printer_silenced' => true]);
    $this->user->settings->refresh();

    expect($this->user->settings->printer_silenced)->toBe(1);

    Livewire::test(PrinterStatus::class)
        ->call('toggle')
        ->assertSee('Drucker aktiv');

    expect($this->user->settings->fresh()->printer_silenced)->toBe(0);
});

it('updates database when toggling printer status', function () {
    Livewire::test(PrinterStatus::class)
        ->call('toggle');

    $this->assertDatabaseHas('user_settings', [
        'user_id' => $this->user->id,
        'printer_silenced' => 1,
    ]);

    Livewire::test(PrinterStatus::class)
        ->call('toggle');

    $this->assertDatabaseHas('user_settings', [
        'user_id' => $this->user->id,
        'printer_silenced' => 0,
    ]);
});

it('immediately reflects status change in UI after toggle', function () {
    $component = Livewire::test(PrinterStatus::class)
        ->assertSee('Drucker aktiv')
        ->assertDontSee('Drucker stumm');

    $component->call('toggle')
        ->assertSee('Drucker stumm')
        ->assertDontSee('Drucker aktiv');

    $component->call('toggle')
        ->assertSee('Drucker aktiv')
        ->assertDontSee('Drucker stumm');
});
