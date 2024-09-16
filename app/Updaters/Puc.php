<?php

namespace App\Updaters;

use App\Exceptions\PucLicenceCheckFailed;
use App\Exceptions\PucNoDownloadLinkException;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Puc extends Abstracts\Updater implements Contracts\Updater
{
    const ENV_VARIABLES = [
        'LICENSE_KEY',
    ];

    public static function name(): string
    {
        return 'YahnisElsts Plugin Update Checker';
    }

    public static function formSchema(): ?Section
    {
        return Forms\Components\Section::make('PuC Details')
            ->statePath('settings')
            ->visible(function ($get) {
                return $get('updater') === 'puc';
            })
            ->schema([
                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required(),
                Forms\Components\TextInput::make('source_url')
                    ->label('Source URL')
                    ->url()
                    ->required(),
                Forms\Components\TextInput::make('endpoint_url')
                    ->label('Endpoint URL')
                    ->url()
                    ->required(),
            ]);
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

        return $errors;
    }

    public function userAgent(): string
    {
        return sprintf('%s; %s',
            config('app.wp_user_agent'),
            $this->package->settings['source_url'],
        );
    }

    private function licenseKey(): string
    {
        return $this->package->environmentVariable('LICENSE_KEY');
    }

    public function doWpAction(string $action)
    {
        $response = Http::withUserAgent($this->userAgent())->get($this->package->settings['endpoint_url'], [
            'wpaction' => $action,
            'dlid' => $this->licenseKey(),
            'wpslug' => $this->package->settings['slug'],
        ]);

        return $response->json();
    }

    protected function fetchPackageInformation(): array
    {
        $licenseCheck = $this->doWpAction('licensecheck');

        if (($licenseCheck['license_check'] ?? true) !== true) {
            throw new PucLicenceCheckFailed;
        }

        $packageInformation = $this->doWpAction('updatecheck');

        if (! isset($packageInformation['download_url']) || $packageInformation === '') {
            throw new PucNoDownloadLinkException;
        }

        $version = $packageInformation['version'];
        $downloadLink = $packageInformation['download_url'];

        return [
            'version' => $version,
            'changelog' => '',
            'downloadLink' => $downloadLink,
        ];
    }
}
