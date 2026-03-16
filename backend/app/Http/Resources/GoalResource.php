<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'target_amount' => (float) $this->target_amount,
            'target_amount_formatted' => 'Rp ' . number_format($this->target_amount, 0, ',', '.'),
            'current_amount' => (float) $this->current_amount,
            'current_amount_formatted' => 'Rp ' . number_format($this->current_amount, 0, ',', '.'),
            'progress_percentage' => $this->progress_percentage,
            'deadline' => $this->deadline?->format('d-m-Y'),
            'deadline_raw' => $this->deadline?->toDateString(),
            'status' => $this->status,
            'transactions' => GoalTransactionResource::collection($this->whenLoaded('goalTransactions')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
