<?php

namespace App\Exceptions;

use Exception;

class DownloadedFileIsPlainTextException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $message = 'Downloaded file is plain text')
    {
        parent::__construct($message);
    }
}
