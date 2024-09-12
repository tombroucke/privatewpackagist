<?php

namespace App\Exceptions;

use Exception;

class WoocommerceSubscriptionNotFoundException extends Exception
{
    public function __construct($message = 'Woocommerce subscription not found')
    {
        parent::__construct($message);
    }
}
