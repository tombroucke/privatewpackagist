<?php

namespace App\Exceptions;

use Exception;

class CouldNotDownloadPackageException extends Exception
{
    public function __construct($link)
    {
        parent::__construct("Could not download package from link: {$link}");
    }
}
