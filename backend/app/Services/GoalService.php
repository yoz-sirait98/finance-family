<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\GoalTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class GoalService
{
    private const TTL = 300; // 5 minutes

    private function cacheKey(int $userId, int $page = 1, int $perPage = 15): string
    {
        return 'goal_' . $userId . '_' . $page . '_' . $perPage;
    }

    public function clearUserCache(int $userId): void
    {
        foreach (range(1, 100) as $page) {
            Cache::forget($this->cacheKey($userId, $page));
        }
    }

    public function __construct(
        private ActivityLogService $activityLogService
    ) {}

    public function list(int $userId, int $page = 1, int $perPage = 15)
    {
        return Cache::remember($this->cacheKey($userId, $page, $perPage), self::TTL, function () use ($userId, $page, $perPage) {
            return Goal::where('user_id', $userId)
                ->with(['goalTransactions', 'account'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);
        });
    }

    public function create(int $userId, array $data): Goal
    {
        return DB::transaction(function () use ($userId, $data) {
            $goal = Goal::create([
                ...$data,
                'user_id' => $userId,
                'current_amount' => 0,
                'status' => 'active',
            ]);

            $this->activityLogService->logCreate(
                $userId,
                'Goal',
                $goal->id,
                $goal->toArray()
            );

            $this->clearUserCache($userId);
            return $goal;
        });
    }

    public function update(Goal $goal, array $data): Goal
    {
        return DB::transaction(function () use ($goal, $data) {
            $beforeData = $goal->toArray();
            $goal->update($data);

            $this->activityLogService->logUpdate(
                $goal->user_id,
                'Goal',
                $goal->id,
                $beforeData,
                $goal->toArray()
            );

            $this->clearUserCache($goal->user_id);
            return $goal;
        });
    }

    public function delete(Goal $goal): void
    {
        DB::transaction(function () use ($goal) {
            $deletedData = $goal->toArray();
            $userId = $goal->user_id;

            $goal->delete();

            $this->activityLogService->logDelete(
                $userId,
                'Goal',
                $goal->id,
                $deletedData
            );

            $this->clearUserCache($userId);
        });
    }

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

            $this->clearUserCache($goal->user_id);
            return $goalTransaction;
        });
    }
}
