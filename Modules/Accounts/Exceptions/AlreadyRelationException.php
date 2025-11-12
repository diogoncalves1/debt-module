<?php

namespace Modules\Accounts\Exceptions;

use Exception;

class AlreadyRelationException extends Exception
{
    protected $message;
    protected $code = 500;

    public function __construct()
    {
        parent::__construct(__('exceptions.alreadyRelationException'), $this->code);
    }
}
