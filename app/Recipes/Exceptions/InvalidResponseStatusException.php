<?php

namespace App\Recipes\Exceptions;

class InvalidResponseStatusException extends RecipeException
{
    /**
     * The error message.
     *
     * @var string
     */
    protected $message = 'The endpoint did not return a successful response status code';
}
