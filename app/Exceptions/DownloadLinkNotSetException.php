<?php

namespace App\Exceptions;

use Exception;

class DownloadLinkNotSetException extends Exception
{
    public function __construct($message = 'Release download link not set')
    {
        parent::__construct($message);
    }
}
