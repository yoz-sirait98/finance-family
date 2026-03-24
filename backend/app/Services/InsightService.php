<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Budget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class InsightService
{
    public function getMonthlyComparison(int $userId): ?string
    {
        return Cache::remember("user_{$userId}_insight_monthly_comp", now()->addMinutes(30), function () use ($userId) {
            $currentMonth = Carbon::now()->startOfMonth();
            $lastMonth = Carbon::now()->subMonth()->startOfMonth();

            $currentExpense = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->whereNull('transfer_id')
                ->whereMonth('transaction_date', $currentMonth->month)
                ->whereYear('transaction_date', $currentMonth->year)
                ->sum('amount');

            $lastExpense = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->whereNull('transfer_id')
                ->whereMonth('transaction_date', $lastMonth->month)
                ->whereYear('transaction_date', $lastMonth->year)
                ->sum('amount');

            if ($lastExpense == 0) {
                return null; // Not enough history to compare
            }

            $diff = $currentExpense - $lastExpense;
            $pct = round((abs($diff) / $lastExpense) * 100);

            if ($diff > 0) {
                return "Your spending increased {$pct}% from last month.";
            } elseif ($diff < 0) {
                return "Your spending decreased {$pct}% from last month. Great job!";
            }

            return "Your spending is exactly the same as last month.";
        });
    }

    public function getTopSpendingCategories(int $userId): ?string
    {
        return Cache::remember("user_{$userId}_insight_top_category", now()->addMinutes(30), function () use ($userId) {
            $topCategory = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->whereNull('transfer_id')
                ->whereMonth('transaction_date', Carbon::now()->month)
                ->whereYear('transaction_date', Carbon::now()->year)
                ->select('category_id', DB::raw('SUM(amount) as total'))
                ->groupBy('category_id')
                ->orderByDesc('total')
                ->with('category')
                ->first();

            if (!$topCategory) {
                return null;
            }

            $totalExpense = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->whereNull('transfer_id')
                ->whereMonth('transaction_date', Carbon::now()->month)
                ->whereYear('transaction_date', Carbon::now()->year)
                ->sum('amount');

            if ($totalExpense == 0) {
                return null;
            }

            $pct = round(($topCategory->total / $totalExpense) * 100);
            $name = $topCategory->category ? $topCategory->category->name : 'Unknown';

            return "{$name} category dominates {$pct}% of your expenses.";
        });
    }

    public function getBudgetRiskPrediction(int $userId): ?string
    {
        // Don't cache budget risk for 30 minutes, make it shorter (e.g. 5 minutes) since it involves current day pacing
        return Cache::remember("user_{$userId}_insight_budget_risk", now()->addMinutes(5), function () use ($userId) {
            $now = Carbon::now();
            $daysInMonth = $now->daysInMonth;
            $currentDay = $now->day;

            $budgets = Budget::where('user_id', $userId)
                ->where('month', $now->month)
                ->where('year', $now->year)
                ->get();

            if ($budgets->isEmpty()) {
                return null; // No budgets set
            }

            $totalBudget = $budgets->sum('amount');
            if ($totalBudget == 0) {
                return null;
            }

            $spentInBudgets = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->whereNull('transfer_id')
                ->whereIn('category_id', $budgets->pluck('category_id'))
                ->whereMonth('transaction_date', $now->month)
                ->whereYear('transaction_date', $now->year)
                ->sum('amount');

            if ($spentInBudgets >= $totalBudget) {
                return "Your total budget has already been exceeded.";
            }

            // Daily rate of spending on budgeted categories
            $dailyRate = $spentInBudgets / $currentDay;

            if ($dailyRate == 0) {
                return null; // Not spending anything yet
            }

            $daysToDeplete = floor($totalBudget / $dailyRate);
            $daysLeft = $daysToDeplete - $currentDay;

            if ($daysToDeplete < $daysInMonth) {
                return "At this rate, your budget will run out in " . max(1, $daysLeft) . " days.";
            }

            return "Your budget is pacing perfectly and will last the entire month.";
        });
    }
}
