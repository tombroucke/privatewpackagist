<?php

namespace App\Exceptions;

use Exception;

class WoocommerceApiRestLimitReachedException extends Exception
{
    public function __construct($message = 'Woocommerce API REST limit reached')
    {
        parent::__construct($message);
    }
}
