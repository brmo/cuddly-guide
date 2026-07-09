<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RfcController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('rfcs', RfcController::class);

    Route::post('/rfcs/{rfc}/approve', [ApprovalController::class, 'approve'])->name('rfcs.approve');
    Route::post('/rfcs/{rfc}/reject', [ApprovalController::class, 'reject'])->name('rfcs.reject');
    Route::post('/rfcs/{rfc}/submit', [RfcController::class, 'submit'])->name('rfcs.submit');
    Route::post('/rfcs/{rfc}/recall', [RfcController::class, 'recall'])->name('rfcs.recall');
    Route::post('/rfcs/{rfc}/resubmit', [RfcController::class, 'resubmit'])->name('rfcs.resubmit');
    Route::post('/rfcs/{rfc}/schedule', [RfcController::class, 'schedule'])->name('rfcs.schedule');
    Route::post('/rfcs/{rfc}/start', [RfcController::class, 'start'])->name('rfcs.start');
    Route::post('/rfcs/{rfc}/complete', [RfcController::class, 'complete'])->name('rfcs.complete');

    Route::resource('users', UserController::class)->except(['show']);

    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
});
