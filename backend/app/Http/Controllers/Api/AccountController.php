<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Http\Resources\AccountResource;
use Illuminate\Http\Request;

class AccountController extends Controller
{
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
            'name' => 'required|string|max:255',
            'type' => 'required|in:bank,cash,ewallet',
            'balance' => 'nullable|numeric|min:0',
            'icon' => 'nullable|string',
        ]);

        $account = Account::create([
            ...$data,
            'user_id' => $request->user()->id,
            'balance' => $data['balance'] ?? 0,
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
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:bank,cash,ewallet',
            'icon' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $account->update($data);

        return new AccountResource($account);
    }

    public function destroy(Request $request, Account $account)
    {
        abort_if($account->user_id !== $request->user()->id, 403);
        $account->delete();

        return response()->json(['message' => 'Account deleted successfully']);
    }
}
