<?php

namespace App\Recipes\Exceptions;

class LicenseCheckFailedException extends RecipeException
{
    /**
     * The error message.
     *
     * @var string
     */
    protected $message = 'License check failed';
}
