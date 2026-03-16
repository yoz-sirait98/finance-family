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

        $transactions = $this->transactionService->createTransfer($data);

        return TransactionResource::collection($transactions);
    }
}
