<?php

namespace App\Exceptions;

use Exception;

class PucLicenceCheckFailed extends Exception
{
    public function __construct()
    {
        parent::__construct('Puc licence check failed');
    }
}
