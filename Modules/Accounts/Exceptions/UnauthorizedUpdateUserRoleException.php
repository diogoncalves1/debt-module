<?php

namespace Modules\Accounts\Exceptions;

use Exception;

class UnauthorizedUpdateUserRoleException extends Exception
{
    protected $message;
    protected $code = 403;

    public function __construct()
    {
        parent::__construct(__('exceptions.unauthorizedUpdateUserRoleException'), $this->code);
    }
}
