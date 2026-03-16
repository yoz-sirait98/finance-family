<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
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
                $request->input('year')
            ),
            'expense-by-member' => $this->dashboardService->getExpenseByMember(
                $userId,
                $request->input('month'),
                $request->input('year')
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
}
