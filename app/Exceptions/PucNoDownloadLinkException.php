<?php

namespace App\Exceptions;

use Exception;

class PucNoDownloadLinkException extends Exception
{
    public function __construct($message = 'No download link found.')
    {
        parent::__construct($message);
    }
}
