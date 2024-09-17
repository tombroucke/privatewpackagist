<?php

namespace App\Exceptions;

use Exception;

class VersionNotSetException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $message = 'Release version not set')
    {
        parent::__construct($message);
    }
}
