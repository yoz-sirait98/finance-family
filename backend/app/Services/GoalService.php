<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\GoalTransaction;
use Illuminate\Support\Facades\DB;

class GoalService
{
    public function contribute(Goal $goal, float $amount, ?string $note = null): GoalTransaction
    {
        return DB::transaction(function () use ($goal, $amount, $note) {
            $goalTransaction = GoalTransaction::create([
                'goal_id' => $goal->id,
                'amount' => $amount,
                'note' => $note,
                'transaction_date' => now()->toDateString(),
            ]);

            $goal->increment('current_amount', $amount);

            // Auto-complete if target reached
            if ($goal->current_amount >= $goal->target_amount) {
                $goal->update(['status' => 'completed']);
            }

            return $goalTransaction;
        });
    }
}
