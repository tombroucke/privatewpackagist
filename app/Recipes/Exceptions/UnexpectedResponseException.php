<?php

namespace App\Recipes\Exceptions;

class UnexpectedResponseException extends RecipeException
{
    /**
     * The error message.
     *
     * @var string
     */
    protected $message = 'The endpoint returned an unexpected response';
}
