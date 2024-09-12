<?php

namespace App\Exceptions;

use Exception;

class WpRocketInvalidRequestException extends Exception
{
    public function __construct($reason)
    {
        parent::__construct("Invalid request: {$reason}");
    }
}
