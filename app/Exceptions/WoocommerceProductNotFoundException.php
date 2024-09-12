<?php

namespace App\Exceptions;

use Exception;

class WoocommerceProductNotFoundException extends Exception
{
    public function __construct($message = 'Woocommerce product not found')
    {
        parent::__construct($message);
    }
}
