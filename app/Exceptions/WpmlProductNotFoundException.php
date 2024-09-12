<?php

namespace App\Exceptions;

use Exception;

class WpmlProductNotFoundException extends Exception
{
    public function __construct($message = 'WPML product not found')
    {
        parent::__construct($message);
    }
}
