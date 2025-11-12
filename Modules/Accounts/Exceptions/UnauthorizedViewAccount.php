<?php

namespace Modules\Accounts\Exceptions;

use Exception;

class UnauthorizedViewAccount extends Exception
{
    protected $message;
    protected $code = 403;

    public function __construct()
    {
        parent::__construct(__('exceptions.unauthorizedViewAccount'), $this->code);
    }
}
