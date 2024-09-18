<?php

namespace App\Recipes\Exceptions;

class NotRespondingException extends RecipeException
{
    /**
     * The error message.
     *
     * @var string
     */
    protected $message = 'The package update endpoint is not responding';
}
