<?php

namespace Modules\Accounts\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Category\Entities\Category;
// use Modules\Currency\Entities\Currency;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\User\Entities\User;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;

    protected $table = 'transactions';
    protected $fillable = ['user_id', 'account_id', 'type', 'amount', 'date', 'description', 'status', 'category_id'/*, 'currency_id'*/];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function sharedRole()
    {
        return $this->hasOneThrough(
            SharedRole::class,
            AccountUser::class,
            'account_id',
            'id',
            'account_id',
            'shared_role_id'
        );
    }
    // public function currency()
    // {
    //     return $this->belongsTo(Currency::class);
    // }


    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
    public function scopeAccount($query, $accountId)
    {
        return $query->where('transactions.account_id', $accountId);
    }
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    public function scopeUser($query, $userId)
    {
        return $query->where('transactions.user_id', $userId);
    }
    public function scopeCategory($query, $categoryId)
    {
        return $query->where("category_id", $categoryId);
    }
}
