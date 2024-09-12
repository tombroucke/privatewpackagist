<?php

namespace App\Exceptions;

use Exception;

class WoocommerceApiNotRespondingException extends Exception
{
    public function __construct($message = 'Woocommerce API server busy')
    {
        parent::__construct($message);
    }
}
