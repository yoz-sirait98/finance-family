<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InsightService;
use Illuminate\Http\Request;

class InsightController extends Controller
{
    public function __construct(
        private InsightService $insightService
    ) {}

    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $insights = [
            'monthly_comparison' => $this->insightService->getMonthlyComparison($userId),
            'savings_rate' => $this->insightService->getSavingsRate($userId),
            'expense_anomaly' => $this->insightService->getExpenseAnomaly($userId),
            'highest_spending_day' => $this->insightService->getHighestSpendingDay($userId),
            'weekend_vs_weekday' => $this->insightService->getWeekendVsWeekdaySpending($userId),
        ];

        // Filter out nulls to only return actionable insights
        $activeInsights = array_filter($insights);

        return response()->json([
            'data' => [
                'insights' => array_values($activeInsights)
            ]
        ]);
    }
}
