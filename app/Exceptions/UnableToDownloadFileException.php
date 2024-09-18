<?php

namespace App\Exceptions;

use Exception;

class UnableToDownloadFileException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $link)
    {
        parent::__construct("Unable to download file from link: {$link}");
    }
}
