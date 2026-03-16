<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\Goal;
use Illuminate\Support\Carbon;

class DashboardService
{
    public function getSummary(int $userId, ?int $month = null, ?int $year = null): array
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $income = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->sum('amount');

        $expense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->sum('amount');

        $totalBalance = Account::where('user_id', $userId)
            ->where('is_active', true)
            ->sum('balance');

        $activeGoals = Goal::where('user_id', $userId)
            ->where('status', 'active')
            ->count();

        return [
            'total_balance' => (float) $totalBalance,
            'monthly_income' => (float) $income,
            'monthly_expense' => (float) $expense,
            'monthly_net' => (float) ($income - $expense),
            'active_goals' => $activeGoals,
            'month' => $month,
            'year' => $year,
        ];
    }

    public function getExpenseByCategory(int $userId, ?int $month = null, ?int $year = null): array
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        return Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->whereNotNull('category_id')
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category:id,name,color,icon')
            ->get()
            ->map(fn ($item) => [
                'category' => $item->category->name ?? 'Uncategorized',
                'color' => $item->category->color ?? '#6c757d',
                'total' => (float) $item->total,
            ])
            ->toArray();
    }

    public function getExpenseByMember(int $userId, ?int $month = null, ?int $year = null): array
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        return Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->whereNotNull('member_id')
            ->selectRaw('member_id, SUM(amount) as total')
            ->groupBy('member_id')
            ->with('member:id,name')
            ->get()
            ->map(function ($item, $index) {
                $colors = ['#0d6efd', '#6f42c1', '#d63384', '#fd7e14', '#20c997', '#0dcaf0'];
                return [
                    'member' => $item->member->name ?? 'Unknown',
                    'color' => $colors[$index % count($colors)],
                    'total' => (float) $item->total,
                ];
            })
            ->toArray();
    }

    public function getIncomeVsExpense(int $userId, ?int $year = null): array
    {
        $year = $year ?? now()->year;
        $data = [];

        for ($m = 1; $m <= 12; $m++) {
            $income = Transaction::where('user_id', $userId)
                ->where('type', 'income')
                ->whereMonth('transaction_date', $m)
                ->whereYear('transaction_date', $year)
                ->sum('amount');

            $expense = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->whereMonth('transaction_date', $m)
                ->whereYear('transaction_date', $year)
                ->sum('amount');

            $data[] = [
                'month' => Carbon::create($year, $m, 1)->format('M'),
                'income' => (float) $income,
                'expense' => (float) $expense,
            ];
        }

        return $data;
    }

    public function getMonthlyExpenseTrend(int $userId, int $months = 6): array
    {
        $data = [];
        $now = now();

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);

            $expense = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->whereMonth('transaction_date', $date->month)
                ->whereYear('transaction_date', $date->year)
                ->sum('amount');

            $data[] = [
                'month' => $date->format('M Y'),
                'expense' => (float) $expense,
            ];
        }

        return $data;
    }
}
