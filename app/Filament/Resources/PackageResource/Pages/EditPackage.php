<?php

namespace App\Filament\Resources\PackageResource\Pages;

use App\Filament\Resources\PackageResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPackage extends EditRecord
{
    protected static string $resource = PackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $tempPackage = $this->record->replicate();
        $tempPackage->fill($this->data);

        $validationErrors = $tempPackage->validationErrors();
        if ($validationErrors->isNotEmpty()) {
            $validationErrors->each(function ($error) {
                Notification::make()
                    ->danger()
                    ->title('Validation Error')
                    ->body($error)
                    ->send();
            });

            $this->halt();
        }
    }
}
