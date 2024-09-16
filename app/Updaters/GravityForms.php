<?php

namespace App\Updaters;

use App\Exceptions\IncorrectApiResponseCodeException;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GravityForms extends Abstracts\Updater implements Contracts\Updater
{
    public static function name(): string
    {
        return 'Gravity Forms';
    }

    public static function formSchema(): ?Section
    {
        return Forms\Components\Section::make('Gravity Forms Details')
            ->statePath('settings')
            ->visible(function ($get) {
                return $get('updater') === 'gravity_forms';
            })
            ->schema([
                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
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

        if (! getenv('GRAVITYFORMS_LICENSE_KEY') !== false) {
            $errors->push('Env. variable GRAVITYFORMS_LICENSE_KEY is required');
        }

        return $errors;
    }

    protected function fetchPackageInformation(): array
    {
        $url = sprintf(
            'https://gravityapi.com/wp-content/plugins/gravitymanager/api.php?op=get_plugin&slug=%s&key=%s',
            $this->package->settings['slug'],
            getenv('GRAVITYFORMS_LICENSE_KEY'),
        );

        $response = Http::get($url);

        if ($response->status() !== 200) {
            throw new IncorrectApiResponseCodeException;
        }

        $body = $response->body();
        $packageInformation = unserialize($body);

        $version = $packageInformation['version'];
        $downloadLink = $packageInformation['download_url_latest'];
        $changelog = $this->extractLatestChangelog($packageInformation['changelog'], 'Gravity Forms v[\d.]+ Changelog\s*-+\s*((?:-.*\n)+)');

        return [
            'version' => $version,
            'changelog' => $changelog,
            'downloadLink' => $downloadLink,
        ];
    }
}
