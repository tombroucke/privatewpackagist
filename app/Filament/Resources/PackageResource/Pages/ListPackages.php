<?php

namespace App\Filament\Resources\PackageResource\Pages;

use App\Filament\Resources\PackageResource;
use App\Filament\Resources\PackageResource\Widgets;
use App\Models\Package;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPackages extends ListRecords
{
    /**
     * The resource this page belongs to.
     */
    protected static string $resource = PackageResource::class;

    /**
     * Get the header actions for the page.
     *
     * @return array<Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->successRedirectUrl(fn (Package $package): string => route('filament.admin.resources.packages.edit', [
                    'record' => $package,
                ])),
        ];
    }

    /**
     * The header widgets.
     */
    protected function getHeaderWidgets(): array
    {
        return [
            Widgets\PackageOverview::class,
        ];
    }
}
