<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MeetingNoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/meetings', [MeetingController::class, 'index'])->name('meetings.index');
    Route::post('/meetings', [MeetingController::class, 'store'])->name('meetings.store');
    Route::get('/meetings/{meeting}', [MeetingController::class, 'show'])->name('meetings.show');
    Route::post('/meetings/{meeting}/start', [MeetingController::class, 'start'])->name('meetings.start');
    Route::post('/meetings/{meeting}/end', [MeetingController::class, 'end'])->name('meetings.end');
    Route::post('/meetings/{meeting}/toggle-lock', [MeetingController::class, 'toggleLock'])->name('meetings.toggle-lock');
    Route::delete('/meetings/{meeting}', [MeetingController::class, 'destroy'])->name('meetings.destroy');

    Route::post('/meetings/{meeting}/notes', [MeetingNoteController::class, 'store'])->name('meetings.notes.store');

    Route::get('/room/{room_code}/join', [RoomController::class, 'joinForm'])->name('rooms.join.form');
    Route::post('/room/{room_code}/join', [RoomController::class, 'join'])->name('rooms.join');
    Route::get('/room/{room_code}', [RoomController::class, 'live'])->name('rooms.live');
    Route::post('/room/{room_code}/leave', [RoomController::class, 'leave'])->name('rooms.leave');
    Route::post('/room/{room_code}/media', [RoomController::class, 'toggleMedia'])->name('rooms.media');
    Route::post('/room/{room_code}/messages', [RoomController::class, 'sendMessage'])->name('rooms.messages');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
