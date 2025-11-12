<?php

namespace Modules\Debts\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounts\Entities\Transaction;
use Modules\Debts\Casts\Money;
use Modules\User\Entities\User;

class DebtPayment extends Model
{
    /** @use HasFactory<\Modules\Debts\Database\Factories\DebtPaymentFactory> */
    use HasFactory;

    protected $fillable = ["transaction_id", "debt_id", "user_id", "paid_at", "status", "amount", "description"];
    protected $casts = [
        'amount' => Money::class
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
    public function debt()
    {
        return $this->belongsTo(Debt::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where("status", $status);
    }
    public function scopeUser($query, $userId)
    {
        return $query->where("user_id", $userId);
    }
    public function scopeDebt($query, $debtId)
    {
        return $query->where("debt_id", $debtId);
    }
}
