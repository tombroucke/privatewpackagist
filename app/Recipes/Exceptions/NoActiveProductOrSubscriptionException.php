<?php

namespace App\Recipes\Exceptions;

class NoActiveProductOrSubscriptionException extends RecipeException
{
    /**
     * The error message.
     *
     * @var string
     */
    protected $message = 'No active product or subscription found';
}
