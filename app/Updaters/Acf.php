<?php

namespace App\Updaters;

use App\Exceptions\AcfFailedToGetLatestVersionException;
use Filament\Forms\Components\Section;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Acf extends Abstracts\Updater
{
    public static function name(): string
    {
        return 'Advanced Custom Fields Pro';
    }

    public static function formSchema(): ?Section
    {
        return null;
    }

    public function fetchPackageTitle(): string
    {
        return Str::of($this->package->slug)
            ->title()
            ->replace('-', ' ')
            ->__toString();
    }

    public function validationErrors(): Collection
    {
        $errors = new Collection;

        if (! getenv('ACF_LICENSE_KEY') !== false) {
            $errors->push('Env. variable ACF_LICENSE_KEY is required');
        }

        return $errors;
    }

    protected function fetchPackageInformation(): array
    {
        $version = $this->getLatestVersion();

        if (! $version) {
            throw new AcfFailedToGetLatestVersionException;
        }

        $downloadLink = sprintf(
            'https://connect.advancedcustomfields.com/v2/plugins/download?t=%s&p=pro&k=%s',
            $version,
            getenv('ACF_LICENSE_KEY'),
        );

        return [
            'version' => $version,
            'changelog' => '',
            'downloadLink' => $downloadLink,
        ];
    }

    private function getLatestVersion()
    {
        $packages = Http::get('https://connect.advancedcustomfields.com/packages.json')->json();

        if (! is_array($packages) || ! isset($packages['packages']['wpengine/advanced-custom-fields-pro'])) {
            return null;
        }

        return array_key_first($packages['packages']['wpengine/advanced-custom-fields-pro']);
    }
}
