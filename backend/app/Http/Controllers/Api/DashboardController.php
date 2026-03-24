<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Services\InsightService;
use App\Services\NetWorthService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private InsightService $insightService,
        private NetWorthService $netWorthService,
    ) {}

    public function summary(Request $request)
    {
        $data = $this->dashboardService->getSummary(
            $request->user()->id,
            $request->input('month'),
            $request->input('year')
        );

        return response()->json(['data' => $data]);
    }

    public function charts(Request $request, string $type)
    {
        $userId = $request->user()->id;

        $data = match ($type) {
            'expense-by-category' => $this->dashboardService->getExpenseByCategory(
                $userId,
                $request->input('month'),
                $request->input('year'),
                $request->input('member_id')
            ),
            'expense-by-member' => $this->dashboardService->getExpenseByMember(
                $userId,
                $request->input('month'),
                $request->input('year'),
                $request->input('member_id')
            ),
            'income-vs-expense' => $this->dashboardService->getIncomeVsExpense(
                $userId,
                $request->input('year')
            ),
            'expense-trend' => $this->dashboardService->getMonthlyExpenseTrend(
                $userId,
                $request->input('months', 6)
            ),
            default => abort(404, 'Chart type not found'),
        };

        return response()->json(['data' => $data]);
    }

    /**
     * Unified full-dashboard endpoint — returns ALL dashboard data in one HTTP round trip.
     */
    public function full(Request $request)
    {
        $userId = $request->user()->id;
        $month  = (int) ($request->input('month') ?? now()->month);
        $year   = (int) ($request->input('year')  ?? now()->year);

        return response()->json([
            'data' => [
                'summary'            => $this->dashboardService->getSummary($userId, $month, $year),
                'expense_by_category'=> $this->dashboardService->getExpenseByCategory($userId, $month, $year),
                'income_vs_expense'  => $this->dashboardService->getIncomeVsExpense($userId, $year),
                'expense_trend'      => $this->dashboardService->getMonthlyExpenseTrend($userId, 6),
                'insights'           => array_values(array_filter([
                    $this->insightService->getMonthlyComparison($userId),
                    $this->insightService->getTopSpendingCategories($userId),
                    $this->insightService->getBudgetRiskPrediction($userId),
                ])),
                'net_worth_current'  => $this->netWorthService->calculateCurrentNetWorth($userId),
                'net_worth_history'  => $this->netWorthService->getHistory($userId),
            ],
        ]);
    }

    /**
     * Unified reports endpoint — returns ALL report chart data in one HTTP request.
     * Reduces 4 HTTP requests to 1 for improved performance.
     */
    public function reports(Request $request)
    {
        $userId = $request->user()->id;
        $month  = (int) ($request->input('month') ?? 0);  // 0 = all months
        $year   = (int) ($request->input('year')  ?? now()->year);
        $memberId = $request->input('member_id') ? (int) $request->input('member_id') : null;

        return response()->json([
            'data' => [
                'expense_by_category' => $this->dashboardService->getExpenseByCategory(
                    $userId,
                    $month ?: null,
                    $year,
                    $memberId
                ),
                'expense_by_member' => $this->dashboardService->getExpenseByMember(
                    $userId,
                    $month ?: null,
                    $year,
                    $memberId
                ),
                'income_vs_expense' => $this->dashboardService->getIncomeVsExpense($userId, $year),
                'expense_trend'     => $this->dashboardService->getMonthlyExpenseTrend($userId, 6),
            ],
        ]);
    }
}

