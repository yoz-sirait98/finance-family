<?php

namespace App\Services;

use App\Models\Account;
use App\Models\NetWorthHistory;
use App\Models\User;
use Illuminate\Support\Carbon;

class NetWorthService
{
    public function calculateCurrentNetWorth(int $userId): float
    {
        return (float) Account::where('user_id', $userId)->sum('balance');
    }

    public function getHistory(int $userId): array
    {
        return \App\Models\NetWorthHistory::where('user_id', $userId)
            ->orderBy('recorded_at', 'asc')
            ->get()
            ->toArray();
    }

    public function storeMonthlySnapshot(): int
    {
        $count = 0;
        $users = User::all();
        // Record at the first day of the current month to normalize snapshots
        $date = now()->startOfMonth()->toDateString();

        foreach ($users as $user) {
            $totalBalance = $this->calculateCurrentNetWorth($user->id);

            NetWorthHistory::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'recorded_at' => $date,
                ],
                [
                    'total_balance' => $totalBalance,
                ]
            );
            $count++;
        }

        return $count;
    }
}
