<?php

namespace Modules\Debts\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Currency\Entities\Currency;
use Modules\User\Entities\User;

class Debt extends Model
{
    /** @use HasFactory<\Modules\Debts\Database\Factories\DebtFactory> */
    use HasFactory;

    protected $fillable = ["name", "total_amount", "paid_amount", "status", "installments", "interest_rate", "start_date", "due_date", "currency_id", "paid_at", "description", "installments_paid"];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, "debts_user", 'debt_id', 'user_id')->withPivot('shared_role_id');
    }
    public function payments()
    {
        return $this->hasMany(DebtPayment::class);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where("status", $status);
    }
}
