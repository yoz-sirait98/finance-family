<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Transaction;

class BudgetService
{
    public function list(int $userId, ?int $month = null, ?int $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $budgets = Budget::where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->with('category')
            ->get();

        if ($budgets->isEmpty()) {
            return $budgets;
        }

        // Single query to get spending for all budget categories at once
        $categoryIds = $budgets->pluck('category_id')->filter()->unique()->values();

        $spentMap = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereNull('transfer_id')
            ->whereIn('category_id', $categoryIds)
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        return $budgets->map(function ($budget) use ($spentMap) {
            $spent = (float) ($spentMap[$budget->category_id] ?? 0);

            $budget->spent = $spent;
            $budget->remaining = max(0, $budget->amount - $spent);
            $budget->percentage = $budget->amount > 0
                ? round(($spent / $budget->amount) * 100, 2)
                : 0;
            $budget->is_over_threshold = $budget->percentage >= 80;

            return $budget;
        });
    }

    public function getAlerts(int $userId, ?int $month = null, ?int $year = null): array
    {
        return $this->list($userId, $month, $year)
            ->filter(fn ($budget) => $budget->is_over_threshold)
            ->values()
            ->toArray();
    }
}
