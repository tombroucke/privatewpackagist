<?php

namespace App\Recipes\Modifiers;

use App\Events\PackageInformationEvent;
use App\Events\RecipeFormsCollectedEvent;
use Filament\Forms;
use Illuminate\Support\Str;

class FilebirdPro implements Contracts\Modifier
{
    /**
     * Adds the email field to the recipe form.
     */
    public function modifyRecipeForms(RecipeFormsCollectedEvent $event): void
    {
        if ($event->recipe === 'App\Recipes\Puc') {
            $event->options->map(function ($option) {
                if ($option->getName() === 'slug') {
                    $option->live();
                }

                return $option;
            });

            $field = Forms\Components\TextInput::make('email')
                ->label('Email address')
                ->email()
                ->visible(function ($get) {
                    return $get('slug') === 'filebird_pro';
                })
                ->required();
            $event->options->push($field);
        }
    }

    /**
     * Adds the license key, domain, and email to the download link.
     */
    public function modifyPackageInformation(PackageInformationEvent $event): void
    {
        if (
            ! $event->package->settings ||
            ! is_array($event->package->settings) ||
            ($event->package->settings['slug'] ?? null) !== 'filebird_pro'
        ) {
            return;
        }

        if (Str::contains($event->packageInformation['downloadLink'], ['code=', 'domain='])) {
            return;
        }

        $code = $event->package->secrets()->get('license_key');
        $email = $event->package->settings['email'];
        $url = $event->package->settings['source_url'];

        $domain = Str::of($url)->replace(['http://', 'https://'], '')->rtrim('/');

        $separatorCharacter = Str::contains($event->packageInformation['downloadLink'], '?')
            ? '&'
            : '?';

        $event->packageInformation['downloadLink'] .= sprintf('%scode=%s&domain=%s&email=%s',
            $separatorCharacter,
            $code,
            $domain,
            $email
        );
    }
}
