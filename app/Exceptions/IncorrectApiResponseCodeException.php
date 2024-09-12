<?php

namespace App\Exceptions;

use Exception;

class IncorrectApiResponseCodeException extends Exception
{
    public function __construct($message = 'Incorrect API response code')
    {
        parent::__construct($message);
    }
}
