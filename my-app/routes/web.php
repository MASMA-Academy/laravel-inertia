<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardItemController;
use App\Http\Controllers\UserManagementController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard Items
    Route::get('dashboard', [DashboardItemController::class, 'index'])->name('dashboard');
    Route::post('dashboard/items', [DashboardItemController::class, 'store'])->name('dashboard.items.store');
    Route::put('dashboard/items/{item}', [DashboardItemController::class, 'update'])->name('dashboard.items.update');
    Route::delete('dashboard/items/{item}', [DashboardItemController::class, 'destroy'])->name('dashboard.items.destroy');
    Route::post('dashboard/items/reorder', [DashboardItemController::class, 'reorder'])->name('dashboard.items.reorder');
    
    // User Management
    Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('users', [UserManagementController::class, 'store'])->name('users.store');
    Route::put('users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    Route::patch('users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
