<?php

namespace App\Exceptions;

use Exception;

class VersionNotSetException extends Exception
{
    public function __construct($message = 'Release version not set')
    {
        parent::__construct($message);
    }
}
