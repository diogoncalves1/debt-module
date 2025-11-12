<?php

namespace Modules\Accounts\Exceptions;

use Exception;

class InviteNotFoundException extends Exception
{
    protected $message;
    protected $code = 404;

    public function __construct()
    {
        parent::__construct(__('exceptions.inviteNotFoundException'), $this->code);
    }
}
