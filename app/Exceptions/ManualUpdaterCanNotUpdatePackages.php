<?php

namespace App\Exceptions;

use Exception;

class ManualUpdaterCanNotUpdatePackages extends Exception
{
    public function __construct($slug)
    {
        parent::__construct("Manual updater can not update packages: {$slug}");
    }
}
