<?php

namespace Modules\Debts\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\User\Entities\User;

class DebtsUser extends Model
{
    /** @use HasFactory<\Database\Factories\DebtsUserFactory> */
    use HasFactory;

    public function debt()
    {
        return $this->belongsTo(Debt::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function shared_role()
    {
        return $this->belongsTo(SharedRole::class);
    }
}
