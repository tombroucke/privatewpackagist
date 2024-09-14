<?php

namespace App\Exceptions;

use Exception;

class WpmlProductNotFoundException extends Exception
{
    public function __construct($product)
    {
        $message = "Product with slug {$product} not found";
        parent::__construct($message);
    }
}
