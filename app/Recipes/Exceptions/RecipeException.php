<?php

namespace App\Recipes\Exceptions;

use App\Recipes\Recipe;
use Exception;

class RecipeException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(protected Recipe $recipe)
    {
        $this->message = "Failed to process recipe [{$recipe::name()}]: {$this->message}.";
    }
}
