<?php

namespace App\Filament\Resources\TokenResource\Pages;

use App\Filament\Resources\TokenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTokens extends ListRecords
{
    protected static string $resource = TokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
