<?php

namespace App\Services;

use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RecurringTransactionService
{
    public function __construct(
        private AccountBalanceService $accountBalanceService
    ) {}

    public function processDue(): int
    {
        $count = 0;
        $dueRecurrings = RecurringTransaction::active()
            ->due()
            ->get();

        foreach ($dueRecurrings as $recurring) {
            DB::transaction(function () use ($recurring, &$count) {
                Transaction::create([
                    'user_id' => $recurring->user_id,
                    'member_id' => $recurring->member_id,
                    'account_id' => $recurring->account_id,
                    'category_id' => $recurring->category_id,
                    'type' => $recurring->type,
                    'amount' => $recurring->amount,
                    'description' => $recurring->description . ' (recurring)',
                    'transaction_date' => $recurring->next_due_date,
                ]);

                $this->accountBalanceService->updateBalance($recurring->account_id);

                // Calculate next due date
                $nextDate = match ($recurring->frequency) {
                    'weekly' => Carbon::parse($recurring->next_due_date)->addWeek(),
                    'monthly' => Carbon::parse($recurring->next_due_date)->addMonth(),
                    'yearly' => Carbon::parse($recurring->next_due_date)->addYear(),
                };

                // Deactivate if past end date
                if ($recurring->end_date && $nextDate->gt($recurring->end_date)) {
                    $recurring->update(['is_active' => false]);
                } else {
                    $recurring->update(['next_due_date' => $nextDate]);
                }

                $count++;
            });
        }

        return $count;
    }
}
