<?php

namespace App\Recipes\Exceptions;

class LatestVersionFailedException extends RecipeException
{
    /**
     * The error message.
     *
     * @var string
     */
    protected $message = 'Failed to get latest version';
}
