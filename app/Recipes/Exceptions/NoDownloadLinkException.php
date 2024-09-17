<?php

namespace App\Recipes\Exceptions;

class NoDownloadLinkException extends RecipeException
{
    /**
     * The error message.
     *
     * @var string
     */
    protected $message = 'No download link found';
}
