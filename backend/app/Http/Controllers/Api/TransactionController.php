<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only([
            'search', 'type', 'category_id', 'member_id',
            'account_id', 'date_from', 'date_to',
            'sort_by', 'sort_dir', 'per_page',
        ]);

        $transactions = $this->transactionService->list(
            $request->user()->id,
            $filters
        );

        return TransactionResource::collection($transactions);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'member_id' => 'required|exists:members,id',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'transaction_date' => 'required|date',
        ]);

        $data['user_id'] = $request->user()->id;

        if ($data['type'] === 'expense') {
            $account = \App\Models\Account::find($data['account_id']);
            if ($account && $account->balance < $data['amount']) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'amount' => ['Insufficient balance in the selected account.']
                ]);
            }
        }

        $transaction = $this->transactionService->create($data);

        return new TransactionResource($transaction);
    }

    public function show(Request $request, Transaction $transaction)
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);

        return new TransactionResource(
            $transaction->load(['member', 'account', 'category', 'attachments'])
        );
    }

    public function update(Request $request, Transaction $transaction)
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'member_id' => 'sometimes|exists:members,id',
            'account_id' => 'sometimes|exists:accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'type' => 'sometimes|in:income,expense',
            'amount' => 'sometimes|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'transaction_date' => 'sometimes|date',
        ]);

        $type = $data['type'] ?? $transaction->type;
        $amount = $data['amount'] ?? $transaction->amount;
        $accountId = $data['account_id'] ?? $transaction->account_id;

        if ($type === 'expense') {
            $account = \App\Models\Account::find($accountId);
            $availableBalance = $account->balance;

            // Adjust available balance based on the previous transaction state
            if ($transaction->account_id == $accountId) {
                if ($transaction->type === 'expense') {
                    $availableBalance += $transaction->amount;
                } elseif ($transaction->type === 'income') {
                    $availableBalance -= $transaction->amount;
                }
            }

            if ($account && $availableBalance < $amount) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'amount' => ['Insufficient balance in the selected account.']
                ]);
            }
        }

        $transaction = $this->transactionService->update($transaction, $data);

        return new TransactionResource($transaction);
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);

        $this->transactionService->delete($transaction);

        return response()->json(['message' => 'Transaction deleted successfully']);
    }

    public function transfer(Request $request)
    {
        $data = $request->validate([
            'member_id' => 'required|exists:members,id',
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'transaction_date' => 'required|date',
        ]);

        $data['user_id'] = $request->user()->id;

        $fromAccount = \App\Models\Account::find($data['from_account_id']);
        if ($fromAccount && $fromAccount->balance < $data['amount']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'amount' => ['Insufficient balance in the source account.']
            ]);
        }

        $transactions = $this->transactionService->createTransfer($data);

        return TransactionResource::collection($transactions);
    }
}
