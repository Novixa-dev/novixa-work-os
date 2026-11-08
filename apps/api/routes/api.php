<?php

use App\Modules\CRM\Controllers\ClientController;
use App\Modules\CRM\Controllers\ClientInteractionController;
use App\Modules\CRM\Controllers\OpportunityController;
use App\Modules\Identity\Controllers\AuthController;
use App\Modules\Identity\Controllers\UserController;
use App\Modules\Knowledge\Controllers\KnowledgeController;
use App\Modules\Notify\Controllers\NotificationController;
use App\Modules\Products\Controllers\ProductController;
use App\Modules\Projects\Controllers\ProjectController;
use App\Modules\Shared\Controllers\DashboardController;
use App\Modules\Shared\Controllers\SearchController;
use App\Modules\Shared\Controllers\SettingsController;
use App\Modules\Work\Controllers\TaskAttachmentController;
use App\Modules\Work\Controllers\TaskCommentController;
use App\Modules\Work\Controllers\TaskController;
use App\Modules\Work\Controllers\TaskDependencyController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Public / Unauthenticated
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('settings/workspace', [SettingsController::class, 'getWorkspaceInfo']);

    // Protected API routes
    Route::middleware('auth:sanctum')->group(function () {

        // Auth & Profile
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::patch('auth/preferences', [AuthController::class, 'updatePreferences']);

        // Users & Team Management
        Route::apiResource('users', UserController::class);
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive']);

        // Projects
        Route::apiResource('projects', ProjectController::class);
        Route::patch('projects/{project}/archive', [ProjectController::class, 'archive']);
        Route::post('projects/{project}/milestones', [ProjectController::class, 'addMilestone']);

        // Tasks & Personal Work
        Route::get('tasks/my-work', [TaskController::class, 'myWork']);
        Route::apiResource('tasks', TaskController::class);
        Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus']);
        Route::patch('tasks/{task}/my-day', [TaskController::class, 'toggleMyDay']);

        // Subtasks & Dependencies
        Route::post('tasks/{task}/dependencies', [TaskDependencyController::class, 'store']);
        Route::delete('tasks/{task}/dependencies/{dependsOnTaskId}', [TaskDependencyController::class, 'destroy']);

        // Task Comments & Attachments
        Route::post('tasks/{task}/comments', [TaskCommentController::class, 'store']);
        Route::delete('tasks/{task}/comments/{comment}', [TaskCommentController::class, 'destroy']);
        Route::post('tasks/{task}/attachments', [TaskAttachmentController::class, 'upload']);
        Route::get('attachments/{attachment}/download', [TaskAttachmentController::class, 'download']);

        // Products
        Route::apiResource('products', ProductController::class);
        Route::get('products/{product}/work', [ProductController::class, 'work']);

        // CRM: Clients & Contacts
        Route::apiResource('clients', ClientController::class);
        Route::get('clients/{client}/interactions', [ClientInteractionController::class, 'index']);
        Route::post('clients/{client}/interactions', [ClientInteractionController::class, 'store']);

        // CRM: Opportunities & Conversion
        Route::apiResource('opportunities', OpportunityController::class);
        Route::patch('opportunities/{opportunity}/stage', [OpportunityController::class, 'updateStage']);
        Route::post('opportunities/{opportunity}/convert-to-project', [OpportunityController::class, 'convertToProject']);

        // Knowledge Base
        Route::get('knowledge', [KnowledgeController::class, 'index']);
        Route::get('knowledge/{slug}', [KnowledgeController::class, 'show']);
        Route::post('knowledge', [KnowledgeController::class, 'store']);

        // Notifications
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead']);

        // Dashboard & Global Search
        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::get('search', [SearchController::class, 'index']);
        Route::put('settings/workspace', [SettingsController::class, 'updateWorkspaceInfo']);
    });
});
