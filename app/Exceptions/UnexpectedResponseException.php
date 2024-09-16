<?php

namespace App\Exceptions;

use Exception;

class UnexpectedResponseException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
