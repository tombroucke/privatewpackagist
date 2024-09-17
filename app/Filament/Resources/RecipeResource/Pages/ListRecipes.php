<?php

namespace App\Filament\Resources\RecipeResource\Pages;

use App\Filament\Resources\RecipeResource;
use Filament\Resources\Pages\ListRecords;

class ListRecipes extends ListRecords
{
    /**
     * The resource this page belongs to.
     */
    protected static string $resource = RecipeResource::class;
}
