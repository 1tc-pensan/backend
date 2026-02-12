<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

// ============================================
// Public Web Routes
// ============================================
Route::get('/', function () {
    return view('welcome');
});

// Login page
Route::get('/login', function () {
    return view('login');
})->name('login');

// ============================================
// Admin Web Interface (Token authentication via JavaScript)
// ============================================
Route::prefix('admin')->group(function () {
    
    // Admin Panel Views (authentication checked via JavaScript + localStorage token)
    Route::view('/users', 'admin.users')->name('admin.users.view');
    Route::view('/tasks', 'admin.tasks')->name('admin.tasks.view');
    Route::view('/assignments', 'admin.assignments')->name('admin.assignments.view');
});
