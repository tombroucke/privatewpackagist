<?php

namespace App\Exceptions;

use Exception;

class EddLicenseCheckFailedException extends Exception
{
    public function __construct($message = 'EDD license check failed')
    {
        parent::__construct($message);
    }
}
