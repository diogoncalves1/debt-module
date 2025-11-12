<?php

namespace Modules\Debts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DebtPaymentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            "debt_id" => "required|debts,id",
            "status" => "required|in:completed,pending",
            "amount" => "required|numeric|min:0",
            "description" => "nullable|string"
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
