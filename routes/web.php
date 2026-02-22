<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\ChatImportController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use LeTraceurSnork\CopyrightYearRange\CopyrightHelper;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'copyrightYear' => CopyrightHelper::getCopyrightString(config('messaga.start_year'))
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
    Route::delete('/api/conversations/{conversation}', [ConversationController::class, 'destroy'])
        ->name('api.conversations.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
