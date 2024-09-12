<?php

namespace App\Exceptions;

use Exception;

class AcfFailedToGetLatestVersionException extends Exception
{
    public function __construct($message = 'ACF failed to get latest version')
    {
        parent::__construct($message);
    }
}
