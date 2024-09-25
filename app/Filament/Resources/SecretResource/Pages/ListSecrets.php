<?php

namespace App\Filament\Resources\SecretResource\Pages;

use App\Filament\Resources\SecretResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSecrets extends ListRecords
{
    protected static string $resource = SecretResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
