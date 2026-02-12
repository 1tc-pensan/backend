<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskAssignmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ============================================
// Public Routes (Nem authentikált)
// ============================================
Route::get('/ping', [AuthController::class, 'ping']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ============================================
// Authenticated Routes
// ============================================
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Profile & Tasks
    Route::get('/profile', [UserController::class, 'profile']);
    Route::get('/my-tasks', [TaskController::class, 'myTasks']);
    Route::patch('/tasks/{id}/status', [TaskController::class, 'updateStatus']);
});

// ============================================
// Admin Routes (Authentikált + Admin)
// ============================================
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    
    // User Management
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    
    
    // Task Management (Full CRUD)
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
    
    
    // Task Assignment Management
    Route::get('/assignments', [TaskAssignmentController::class, 'index']);
    Route::post('/assignments', [TaskAssignmentController::class, 'store']);
    Route::get('/assignments/{id}', [TaskAssignmentController::class, 'show']);
    Route::put('/assignments/{id}', [TaskAssignmentController::class, 'update']);
    Route::delete('/assignments/{id}', [TaskAssignmentController::class, 'destroy']);
    
    // ToDo: Restore route megírása soft deletelt erőforrásokhoz (users, tasks, assignments)


    // Helper Routes
    Route::get('/tasks/{taskId}/assignments', [TaskAssignmentController::class, 'byTask']);
    Route::get('/users/{userId}/assignments', [TaskAssignmentController::class, 'byUser']);
});