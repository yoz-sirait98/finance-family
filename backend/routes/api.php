<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\GoalController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\RecurringTransactionController;
use App\Http\Controllers\Api\NetWorthController;
use App\Http\Controllers\Api\InsightController;

/* |-------------------------------------------------------------------------- | API Routes — Family Finance Management System |-------------------------------------------------------------------------- | Base prefix: /api */

// Public routes
Route::post('/auth/register', [AuthController::class , 'register']);
Route::post('/auth/login', [AuthController::class , 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class , 'logout']);
    Route::get('/auth/profile', [AuthController::class , 'profile']);
    Route::put('/auth/password', [AuthController::class , 'changePassword']);

    // Members
    Route::apiResource('members', MemberController::class);
    Route::patch('/members/{member}/toggle-active', [MemberController::class , 'toggleActive']);

    // Accounts
    Route::apiResource('accounts', AccountController::class);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Transactions
    Route::post('/transactions/transfer', [TransactionController::class , 'transfer']);
    Route::apiResource('transactions', TransactionController::class);

    // Budgets
    Route::get('/budgets/alerts', [BudgetController::class , 'alerts']);
    Route::apiResource('budgets', BudgetController::class);

    // Goals
    Route::post('/goals/{goal}/contribute', [GoalController::class , 'contribute']);
    Route::apiResource('goals', GoalController::class);

    // Recurring Transactions
    Route::apiResource('recurring-transactions', RecurringTransactionController::class);

    // Dashboard
    Route::get('/dashboard/summary', [DashboardController::class , 'summary']);
    Route::get('/dashboard/charts/{type}', [DashboardController::class , 'charts']);
    Route::get('/dashboard/full', [DashboardController::class , 'full']);

    // Net Worth Tracking
    Route::get('/net-worth/current', [NetWorthController::class , 'current']);
    Route::get('/net-worth/history', [NetWorthController::class , 'history']);

    // Financial Insights Engine
    Route::get('/insights', [InsightController::class , 'index']);
});
