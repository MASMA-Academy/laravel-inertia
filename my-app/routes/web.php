<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardItemController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardItemController::class, 'index'])->name('dashboard');
    Route::post('dashboard/items', [DashboardItemController::class, 'store'])->name('dashboard.items.store');
    Route::put('dashboard/items/{item}', [DashboardItemController::class, 'update'])->name('dashboard.items.update');
    Route::delete('dashboard/items/{item}', [DashboardItemController::class, 'destroy'])->name('dashboard.items.destroy');
    Route::post('dashboard/items/reorder', [DashboardItemController::class, 'reorder'])->name('dashboard.items.reorder');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
