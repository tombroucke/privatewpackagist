<?php

namespace App\Filament\Resources\ReleaseResource\Pages;

use App\Filament\Resources\ReleaseResource;
use App\Filament\Resources\ReleaseResource\Widgets;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReleases extends ListRecords
{
    /**
     * The resource this page belongs to.
     */
    protected static string $resource = ReleaseResource::class;

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

    /**
     * The header widgets.
     */
    protected function getHeaderWidgets(): array
    {
        return [
            Widgets\ReleaseOverview::class,
        ];
    }
}
