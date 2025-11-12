<?php

namespace Modules\Debts\Http\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Currency\Http\Transformers\CurrencyResource;

class DebtResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'totalAmount' => $this->total_amount,
            'paidAmount' => $this->paid_amount,
            'status' => $this->status,
            'installments' => $this->installments,
            'insterestRate' => $this->interest_rate,
            'startDate' => $this->start_date,
            'dueDate' => $this->due_date,
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'paidAt' => $this->paid_at,
            'actions' => $this->actions ?? null,
            // 'creator' => new UserResource($this->whenLoaded('creator')),
            // 'payments' => DebtPaymentResource::collection($this->whenLoaded('payments'))
        ];
    }
}
