<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RecurringTransaction;
use App\Http\Resources\RecurringTransactionResource;
use Illuminate\Http\Request;

class RecurringTransactionController extends Controller
{
    public function index(Request $request)
    {
        $recurring = RecurringTransaction::where('user_id', $request->user()->id)
            ->with(['member', 'account', 'category'])
            ->orderBy('next_due_date')
            ->get();

        return RecurringTransactionResource::collection($recurring);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'member_id' => 'required|exists:members,id',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'frequency' => 'required|in:weekly,monthly,yearly',
            'next_due_date' => 'required|date',
            'end_date' => 'nullable|date|after:next_due_date',
        ]);

        $recurring = RecurringTransaction::create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        return new RecurringTransactionResource($recurring->load(['member', 'account', 'category']));
    }

    public function show(Request $request, RecurringTransaction $recurringTransaction)
    {
        abort_if($recurringTransaction->user_id !== $request->user()->id, 403);
        return new RecurringTransactionResource(
            $recurringTransaction->load(['member', 'account', 'category'])
        );
    }

    public function update(Request $request, RecurringTransaction $recurringTransaction)
    {
        abort_if($recurringTransaction->user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'member_id' => 'sometimes|exists:members,id',
            'account_id' => 'sometimes|exists:accounts,id',
            'category_id' => 'sometimes|exists:categories,id',
            'type' => 'sometimes|in:income,expense',
            'amount' => 'sometimes|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'frequency' => 'sometimes|in:weekly,monthly,yearly',
            'next_due_date' => 'sometimes|date',
            'end_date' => 'nullable|date',
            'is_active' => 'sometimes|boolean',
        ]);

        $recurringTransaction->update($data);

        return new RecurringTransactionResource(
            $recurringTransaction->load(['member', 'account', 'category'])
        );
    }

    public function destroy(Request $request, RecurringTransaction $recurringTransaction)
    {
        abort_if($recurringTransaction->user_id !== $request->user()->id, 403);
        $recurringTransaction->delete();

        return response()->json(['message' => 'Recurring transaction deleted successfully']);
    }
}
