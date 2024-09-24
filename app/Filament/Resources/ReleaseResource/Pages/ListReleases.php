<?php

namespace App\Filament\Resources\ReleaseResource\Pages;

use App\Filament\Resources\ReleaseResource;
use App\Filament\Resources\ReleaseResource\Widgets;
use App\Models\Package;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    return $this->moveUpload($data);
                }),
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

    /**
     * Move uploaded file to the correct location.
     */
    private function moveUpload(array $data): array
    {
        if ($data['path'] instanceof TemporaryUploadedFile) {
            $package = Package::findOrFail($data['package_id']);

            $path = $package->generateReleasePath($data['version']);

            $data['path']->storeAs('packages', $path);
            $data['path'] = $path;
        } else {
            $data['path'] = preg_replace('/^packages\//', '', $data['path']);
        }

        return $data;
    }
}
