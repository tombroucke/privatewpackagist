<?php

namespace App\Recipes\Exceptions;

use Exception;

class CouldNotAuthenticateException extends Exception
{
    public function __construct($message = 'Could not authenticate with the remote server')
    {
        parent::__construct($message);
    }
}
