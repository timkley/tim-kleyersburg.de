<?php

declare(strict_types=1);

use App\Http\Middleware\BearerToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Modules\Holocron\Quest\Controller\Api\QuestAttachmentController;
use Modules\Holocron\Quest\Controller\Api\QuestController;
use Modules\Holocron\Quest\Controller\Api\QuestLinkController;
use Modules\Holocron\Quest\Controller\Api\QuestNoteController;
use Modules\Holocron\Quest\Controller\Api\QuestRecurrenceController;
use Modules\Holocron\Quest\Controller\Api\QuestReminderController;

Route::middleware([BearerToken::class, SubstituteBindings::class])
    ->name('holocron.api.quests.')
    ->prefix('api/holocron/quests')
    ->group(function () {
        Route::get('/', [QuestController::class, 'index'])->name('index');
        Route::post('/', [QuestController::class, 'store'])->name('store');
        Route::get('/{quest}', [QuestController::class, 'show'])->name('show');
        Route::patch('/{quest}', [QuestController::class, 'update'])->name('update');
        Route::delete('/{quest}', [QuestController::class, 'destroy'])->name('destroy');
        Route::post('/{quest}/complete', [QuestController::class, 'complete'])->name('complete');
        Route::post('/{quest}/move', [QuestController::class, 'move'])->name('move');
        Route::post('/{quest}/print', [QuestController::class, 'print'])->name('print');
        Route::post('/{quest}/accept', [QuestController::class, 'accept'])->name('accept');

        Route::post('/{quest}/attachments', [QuestAttachmentController::class, 'store'])->name('attachments.store');
        Route::delete('/{quest}/attachments', [QuestAttachmentController::class, 'destroy'])->name('attachments.destroy');

        Route::get('/{quest}/notes', [QuestNoteController::class, 'index'])->name('notes.index');
        Route::post('/{quest}/notes', [QuestNoteController::class, 'store'])->name('notes.store');
        Route::delete('/{quest}/notes/{note}', [QuestNoteController::class, 'destroy'])->name('notes.destroy');

        Route::get('/{quest}/links', [QuestLinkController::class, 'index'])->name('links.index');
        Route::post('/{quest}/links', [QuestLinkController::class, 'store'])->name('links.store');
        Route::delete('/{quest}/links/{pivotId}', [QuestLinkController::class, 'destroy'])->name('links.destroy');

        Route::get('/{quest}/reminders', [QuestReminderController::class, 'index'])->name('reminders.index');
        Route::post('/{quest}/reminders', [QuestReminderController::class, 'store'])->name('reminders.store');
        Route::delete('/{quest}/reminders/{reminder}', [QuestReminderController::class, 'destroy'])->name('reminders.destroy');

        Route::get('/{quest}/recurrence', [QuestRecurrenceController::class, 'show'])->name('recurrence.show');
        Route::post('/{quest}/recurrence', [QuestRecurrenceController::class, 'store'])->name('recurrence.store');
        Route::delete('/{quest}/recurrence', [QuestRecurrenceController::class, 'destroy'])->name('recurrence.destroy');
    });
