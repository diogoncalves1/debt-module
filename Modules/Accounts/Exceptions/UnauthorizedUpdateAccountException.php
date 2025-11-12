<?php

namespace Modules\Accounts\Exceptions;

use Exception;

class UnauthorizedUpdateAccountException extends Exception
{
    protected $message;
    protected $code = 403;

    public function __construct()
    {
        parent::__construct(__('exceptions.unauthorizedUpdateAccountException'), $this->code);
    }
}
