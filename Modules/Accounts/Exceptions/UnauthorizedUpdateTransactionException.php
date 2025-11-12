<?php

namespace Modules\Accounts\Exceptions;

use Exception;

class UnauthorizedUpdateTransactionException extends Exception
{
    protected $message;
    protected $code = 403;

    public function __construct()
    {
        parent::__construct(__('exceptions.unauthorizedUpdateTransactionException'), $this->code);
    }
}
