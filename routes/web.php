<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\ChatImportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use LeTraceurSnork\CopyrightYearRange\CopyrightHelper;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'      => Route::has('login'),
        'canRegister'   => Route::has('register'),
        'copyrightYear' => CopyrightHelper::getCopyrightString(config('messaga.start_year')),
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/api/import/chats', [ChatImportController::class, 'store'])
        ->name('api.import.chats');

    Route::get('/api/conversations', [ConversationController::class, 'index'])
        ->name('api.conversations.index');
    Route::get('/api/conversations/{conversation}/messages', [ConversationController::class, 'messages'])
        ->name('api.conversations.messages');
    Route::get('/api/conversations/{conversation}/messages/{messageId}/attachment', [ConversationController::class, 'attachment'])
        ->name('api.conversations.messages.attachment');
    Route::post('/api/conversations/{conversation}/media', [ConversationController::class, 'uploadMedia'])
        ->name('api.conversations.media.upload');
    Route::delete('/api/conversations/{conversation}', [ConversationController::class, 'destroy'])
        ->name('api.conversations.destroy');
});

require __DIR__ . '/auth.php';
