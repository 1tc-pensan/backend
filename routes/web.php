<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

// ============================================
// Public Web Routes
// ============================================
Route::get('/', function () {
    return view('welcome');
});

// ============================================
// Admin Web Interface (Auth + Admin middleware)
// ============================================
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    
    // Admin Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    
    // Admin Panel Views (optional - for future web interface)
    Route::view('/users', 'admin.users')->name('admin.users.view');
    Route::view('/tasks', 'admin.tasks')->name('admin.tasks.view');
    Route::view('/assignments', 'admin.assignments')->name('admin.assignments.view');
});
