<?php

namespace App\Exceptions;

use Exception;

class InvalidFileTypeException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $fileType)
    {
        parent::__construct("The file type '{$fileType}' is invalid.");
    }
}
