<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;

class AccountBalanceService
{
    public function updateBalance(int $accountId): void
    {
        $account = Account::findOrFail($accountId);

        $income = Transaction::where('account_id', $accountId)
            ->where('type', 'income')
            ->sum('amount');

        $expense = Transaction::where('account_id', $accountId)
            ->where('type', 'expense')
            ->sum('amount');

        $account->update([
            'balance' => $account->initial_balance + $income - $expense,
        ]);
    }

    public function recalculateAll(int $userId): void
    {
        $accounts = Account::where('user_id', $userId)->get();
        foreach ($accounts as $account) {
            $this->updateBalance($account->id);
        }
    }
}
