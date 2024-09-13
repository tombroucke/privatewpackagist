<?php

namespace App\Exceptions;

use Exception;

class UnableToDownloadFileException extends Exception
{
    public function __construct($link)
    {
        parent::__construct("Unable to download file from link: {$link}");
    }
}
