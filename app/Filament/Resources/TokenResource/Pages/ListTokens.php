<?php

namespace App\Filament\Resources\TokenResource\Pages;

use App\Filament\Resources\TokenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTokens extends ListRecords
{
    /**
     * The resource this page belongs to.
     */
    protected static string $resource = TokenResource::class;

    /**
     * Get the header actions for the page.
     *
     * @return array<Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
