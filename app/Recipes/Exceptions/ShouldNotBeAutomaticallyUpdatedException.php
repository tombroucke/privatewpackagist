<?php

namespace App\Recipes\Exceptions;

use Exception;

class ShouldNotBeAutomaticallyUpdatedException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $link)
    {
        parent::__construct("Package should not be updated automatically: {$link}");
    }
}
