<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NetWorthHistory;
use App\Services\NetWorthService;
use Illuminate\Http\Request;

class NetWorthController extends Controller
{
    public function __construct(
        private NetWorthService $netWorthService
    ) {}

    public function current(Request $request)
    {
        $userId = $request->user()->id;
        $total = $this->netWorthService->calculateCurrentNetWorth($userId);

        return response()->json([
            'data' => [
                'current_net_worth' => $total,
            ]
        ]);
    }

    public function history(Request $request)
    {
        $userId = $request->user()->id;

        $history = NetWorthHistory::where('user_id', $userId)
            ->orderBy('recorded_at', 'asc')
            ->get();

        return response()->json([
            'data' => $history
        ]);
    }
}
