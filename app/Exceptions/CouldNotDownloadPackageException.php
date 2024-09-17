<?php

namespace App\Exceptions;

use Exception;

class CouldNotDownloadPackageException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $link)
    {
        parent::__construct("Could not download package from link: {$link}");
    }
}
