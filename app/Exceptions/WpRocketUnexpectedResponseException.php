<?php

namespace App\Exceptions;

use Exception;

class WpRocketUnexpectedResponseException extends Exception
{
    public function __construct($message = 'WP Rocket API returned an unexpected response')
    {
        parent::__construct($message);
    }
}
