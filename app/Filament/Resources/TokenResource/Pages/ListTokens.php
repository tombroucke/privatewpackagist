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
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['deactivated_at'] = $data['deactivated_at'] ? null : now();

                    return $data;
                }),
        ];
    }
}
