<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\AccountBalanceService;
use App\Http\Resources\AccountResource;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(
        private AccountBalanceService $accountBalanceService
    ) {}

    public function index(Request $request)
    {
        $accounts = Account::where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get();

        return AccountResource::collection($accounts);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'required|in:bank,cash,ewallet',
            'initial_balance' => 'nullable|numeric|min:0',
            'icon'            => 'nullable|string',
        ]);

        $initialBalance = $data['initial_balance'] ?? 0;

        $account = Account::create([
            'user_id'         => $request->user()->id,
            'name'            => $data['name'],
            'type'            => $data['type'],
            'icon'            => $data['icon'] ?? null,
            'initial_balance' => $initialBalance,
            'balance'         => $initialBalance,  // current balance starts equal to initial
        ]);

        return new AccountResource($account);
    }

    public function show(Request $request, Account $account)
    {
        abort_if($account->user_id !== $request->user()->id, 403);
        return new AccountResource($account);
    }

    public function update(Request $request, Account $account)
    {
        abort_if($account->user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'name'            => 'sometimes|string|max:255',
            'type'            => 'sometimes|in:bank,cash,ewallet',
            'icon'            => 'nullable|string',
            'is_active'       => 'sometimes|boolean',
            'initial_balance' => 'sometimes|numeric|min:0',
        ]);

        $account->update($data);

        // If initial_balance was changed, recalculate the running balance
        if (isset($data['initial_balance'])) {
            $this->accountBalanceService->updateBalance($account->id);
            $account->refresh();
        }

        return new AccountResource($account);
    }

    public function destroy(Request $request, Account $account)
    {
        abort_if($account->user_id !== $request->user()->id, 403);
        $account->delete();

        return response()->json(['message' => 'Account deleted successfully']);
    }
}
