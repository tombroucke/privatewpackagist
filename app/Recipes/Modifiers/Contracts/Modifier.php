<?php

namespace App\Recipes\Modifiers\Contracts;

use App\Events\PackageInformationEvent;
use App\Events\RecipeFormsCollectedEvent;

interface Modifier
{
    public function modifyRecipeForms(RecipeFormsCollectedEvent $event): void;

    public function modifyPackageInformation(PackageInformationEvent $event): void;
}
