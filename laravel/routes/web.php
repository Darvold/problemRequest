<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DispatcherController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\RequestController;
use Illuminate\Support\Facades\Route;


// Маршруты авторизации
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Маршруты для диспетчера
Route::middleware(['auth', 'role:dispatcher'])->prefix('dispatcher')->group(function () {
    Route::get('/dashboard', [DispatcherController::class, 'dashboard'])->name('dispatcher.dashboard');
    Route::post('/requests/{id}/assign', [DispatcherController::class, 'assignMaster'])->name('dispatcher.assign');
});
Route::middleware(['auth', 'role:dispatcher'])->prefix('requests')->name('requests.')->group(function () {
    Route::get('/create', [RequestController::class, 'create'])->name('create');
    Route::post('/', [RequestController::class, 'store'])->name('store');
});
// Маршруты для мастера
Route::middleware(['auth', 'role:master'])->prefix('master')->name('master.')->group(function () {
    Route::get('/dashboard', [MasterController::class, 'dashboard'])->name('dashboard');
    Route::get('/requests/{id}', [MasterController::class, 'showRequest'])->name('request');
    Route::post('/requests/{id}/status', [MasterController::class, 'updateStatus'])->name('update-status');

});

// Общий дашборд (для теста)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');
