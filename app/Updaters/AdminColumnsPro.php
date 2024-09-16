<?php

namespace App\Updaters;

use App\Exceptions\UnexpectedResponseException;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AdminColumnsPro extends Abstracts\Updater
{
    public static function name(): string
    {
        return 'Admin Columns Pro';
    }

    public static function formSchema(): ?Section
    {
        return Forms\Components\Section::make('Admin Columns Pro Details')
            ->statePath('settings')
            ->visible(function ($get) {
                return $get('updater') === 'admin_columns_pro';
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

        if (! getenv('ADMIN_COLUMNS_PRO_LICENSE_KEY') !== false) {
            $errors->push('Env. variable ADMIN_COLUMNS_PRO_LICENSE_KEY is required');
        }

        return $errors;
    }

    protected function fetchPackageInformation(): array
    {

        [$downloadLink, $changelog] = $this->getDownloadLinkAndChangelog();

        return [
            'version' => $this->getVersion(),
            'changelog' => $changelog,
            'downloadLink' => $downloadLink,
        ];
    }

    private function getDownloadLinkAndChangelog(): array
    {
        $response = $this->doRequest([
            'command' => 'products_update',
            'plugin_name' => $this->package->settings['slug'],
            'subscription_key' => getenv('ADMIN_COLUMNS_PRO_LICENSE_KEY'),
        ]);

        if (! isset($response['admin-columns-pro']['package'])) {
            throw new UnexpectedResponseException('Download link not found in response');
        }

        return [$response['admin-columns-pro']['package'], ($response['admin-columns-pro']['sections']['changelog'] ?? '')];
    }

    private function getVersion(): string
    {
        $response = $this->doRequest([
            'command' => 'product_information',
            'plugin_name' => $this->package->settings['slug'],
        ]);

        if (! isset($response['version'])) {
            throw new UnexpectedResponseException('Version not found in response');
        }

        return $response['version'];
    }

    private function doRequest(array $args)
    {
        return Http::asForm()
            ->withUserAgent($this->userAgent())
            ->withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])
            ->post('https://www.admincolumns.com', $args)
            ->json();
    }
}
