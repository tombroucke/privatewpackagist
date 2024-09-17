<?php

namespace App\Filament\Resources\PackageResource\Pages;

use App\Filament\Resources\PackageResource;
use Filament\Actions;
use Filament\Notifications\Notification;
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
     * Handle actions before saving a record.
     */
    protected function beforeSave(): void
    {
        $package = $this->record->replicate();
        $package->fill($this->data);

        $errors = $package->validationErrors();

        if ($errors->isNotEmpty()) {
            $errors->each(fn ($error) => Notification::make()
                ->danger()
                ->title('Validation Error')
                ->body($error)
                ->send()
            );

            $this->halt();
        }
    }
}
