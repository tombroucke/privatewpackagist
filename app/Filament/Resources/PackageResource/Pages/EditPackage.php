<?php

namespace App\Filament\Resources\PackageResource\Pages;

use App\Filament\Resources\PackageResource;
use App\Filament\Resources\PackageResource\Widgets;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPackage extends EditRecord
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
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * The header widgets.
     */
    protected function getHeaderWidgets(): array
    {
        $widgets = [];
        if (! $this->record->valid) {
            $widgets[] = Widgets\PackageLicenseStatus::class;
        }

        return $widgets;
    }

    /**
     * Handle actions after saving a package.
     */
    protected function afterSave(): void
    {
        $this->dispatch('refreshRelation', 'releases');
    }

    /**
     * Mutate the form data before saving.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->record->updated_at = now(); // Make sure the updated() method of the observer is called.

        return $data;
    }
}
