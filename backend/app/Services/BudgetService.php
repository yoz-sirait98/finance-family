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

        return Budget::where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->with('category')
            ->get()
            ->map(function ($budget) use ($userId, $month, $year) {
                $spent = Transaction::where('user_id', $userId)
                    ->where('category_id', $budget->category_id)
                    ->where('type', 'expense')
                    ->whereMonth('transaction_date', $month)
                    ->whereYear('transaction_date', $year)
                    ->sum('amount');

                $budget->spent = (float) $spent;
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
