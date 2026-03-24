<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class BudgetService
{
    public function __construct(
        private ActivityLogService $activityLogService
    ) {}

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

    public function createOrUpdate(int $userId, array $data): Budget
    {
        return DB::transaction(function () use ($userId, $data) {
            $budget = Budget::where('user_id', $userId)
                ->where('category_id', $data['category_id'])
                ->where('month', $data['month'])
                ->where('year', $data['year'])
                ->first();

            if ($budget) {
                $beforeData = $budget->toArray();
                $budget->update(['amount' => $data['amount']]);

                $this->activityLogService->logUpdate(
                    $userId,
                    'Budget',
                    $budget->id,
                    $beforeData,
                    $budget->toArray()
                );
            } else {
                $budget = Budget::create([
                    'user_id' => $userId,
                    'category_id' => $data['category_id'],
                    'amount' => $data['amount'],
                    'month' => $data['month'],
                    'year' => $data['year'],
                ]);

                $this->activityLogService->logCreate(
                    $userId,
                    'Budget',
                    $budget->id,
                    $budget->toArray()
                );
            }

            return $budget->load('category');
        });
    }

    public function update(Budget $budget, array $data): Budget
    {
        return DB::transaction(function () use ($budget, $data) {
            $beforeData = $budget->toArray();
            $budget->update($data);

            $this->activityLogService->logUpdate(
                $budget->user_id,
                'Budget',
                $budget->id,
                $beforeData,
                $budget->toArray()
            );

            return $budget->load('category');
        });
    }

    public function delete(Budget $budget): void
    {
        DB::transaction(function () use ($budget) {
            $deletedData = $budget->toArray();
            $userId = $budget->user_id;

            $budget->delete();

            $this->activityLogService->logDelete(
                $userId,
                'Budget',
                $budget->id,
                $deletedData
            );
        });
    }
}

