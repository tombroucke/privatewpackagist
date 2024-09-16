<?php

namespace App\Updaters;

use App\Exceptions\WpmlProductNotFoundException;
use App\Updaters\Abstracts\Updater;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Wpml extends Updater implements Contracts\Updater
{
    public static function name(): string
    {
        return 'WPML';
    }

    public static function formSchema(): ?Section
    {
        return Forms\Components\Section::make('WPML Details')
            ->statePath('settings')
            ->visible(function ($get) {
                return $get('updater') === 'wpml';
            })
            ->schema([
                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required(),
            ]);
    }

    public function fetchPackageTitle(): string
    {
        $product = $this->getProduct($this->package->slug);

        $name = $product['name'] ?? Str::of($this->package->slug)
            ->title()
            ->replace('-', ' ')
            ->__toString();

        return strip_tags($name);
    }

    public function validationErrors(): Collection
    {
        $errors = new Collection;

        if (! getenv('WPML_USER_ID') !== false) {
            $errors->push('Env. variable WPML_USER_ID is required');
        }

        if (! getenv('WPML_LICENSE_KEY') !== false) {
            $errors->push('Env. variable WPML_LICENSE_KEY is required');
        }

        return $errors;
    }

    private function getProduct($slug)
    {
        $response = Http::get('http://d2salfytceyqoe.cloudfront.net/wpml33-products.json');
        $body = $response->body();

        $products = json_decode($body, true);
        if (! is_array($products) || ! isset($products['downloads']['plugins'])) {
            return null;
        }

        return collect($products['downloads']['plugins'])->firstWhere('slug', $slug);
    }

    protected function fetchPackageInformation(): array
    {
        $product = $this->getProduct($this->package->settings['slug']);
        if (! $product) {
            throw new WpmlProductNotFoundException($this->package->settings['slug']);
        }

        $version = $product['version'];
        $changelog = $this->extractLatestChangelog($product['changelog'] ?? '', '#### (\d+\.\d+\.\d+)(?:\s*\n\n)?(.*?)(?=\n\n#### \d+\.\d+\.\d+|$)');
        $downloadLink = sprintf(
            $product['url'].'&user_id=%s&subscription_key=%s',
            getenv('WPML_USER_ID'),
            getenv('WPML_LICENSE_KEY'),
        );

        return [
            'version' => $version,
            'changelog' => $changelog,
            'downloadLink' => $downloadLink,
        ];
    }
}
