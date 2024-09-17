<?php

namespace App\Recipes\Exceptions;

class RateLimitReachedException extends RecipeException
{
    /**
     * The error message.
     *
     * @var string
     */
    protected $message = 'The recipe endpoint has reached a rate limit';
}
