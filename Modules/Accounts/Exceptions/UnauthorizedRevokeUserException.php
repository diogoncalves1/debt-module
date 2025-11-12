<?php

namespace Modules\Accounts\Exceptions;

use Exception;

class UnauthorizedRevokeUserException extends Exception
{
    protected $message;
    protected $code = 403;

    public function __construct()
    {
        parent::__construct(__('exceptions.unauthorizedRevokeUserException'), $this->code);
    }
}
