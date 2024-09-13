<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AccountPage extends Page
{
    protected static ?string $slug = 'profile';

    protected static string $view = 'filament-breezy::filament.pages.my-profile';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 99;

    public function getTitle(): string
    {
        return __('filament-breezy::default.profile.my_profile');
    }

    public function getHeading(): string
    {
        return __('filament-breezy::default.profile.my_profile');
    }

    public function getSubheading(): ?string
    {
        return __('filament-breezy::default.profile.subheading') ?? null;
    }

    public static function getSlug(): string
    {
        return filament('filament-breezy')->slug();
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-breezy::default.profile.profile');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return filament('filament-breezy')->shouldRegisterNavigation('myProfile');
    }

    public function getRegisteredMyProfileComponents(): array
    {
        return filament('filament-breezy')->getRegisteredMyProfileComponents();
    }
}
