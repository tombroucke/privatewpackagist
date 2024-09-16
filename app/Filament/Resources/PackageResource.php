<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackageResource\Pages;
use App\Filament\Resources\PackageResource\RelationManagers\ReleasesRelationManager;
use App\Models\Package;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Packages';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('updater')
                    ->required()
                    ->options([
                        'acf' => 'ACF',
                        'admin_columns_pro' => 'Admin Columns Pro',
                        'direct' => 'Direct',
                        'edd' => 'Easy Digital Downloads',
                        'gravity_forms' => 'Gravity Forms',
                        'manual' => 'Manual',
                        'wp_rocket' => 'WP Rocket',
                        'wpml' => 'WPML',
                        'woocommerce' => 'WooCommerce',
                        'puc' => 'YahnisElsts Plugin Update Checker',
                    ])
                    ->reactive()
                    ->native(false)
                    ->searchable()
                    ->afterStateUpdated(function (callable $set, $state) {
                        $set('updater', $state);
                    }),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'wordpress-plugin' => 'WordPress Plugin',
                        'wordpress-muplugin' => 'WordPress MU Plugin',
                        'wordpress-theme' => 'WordPress Theme',
                    ])
                    ->native(false)
                    ->searchable()
                    ->default('wordpress-plugin'),

                // Conditionally display fields for EDD
                Forms\Components\Section::make('EDD Details')
                    ->statePath('settings')
                    ->visible(function ($get) {
                        return $get('updater') === 'edd';
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
                        Forms\Components\Select::make('method')
                            ->label('Method')
                            ->options([
                                'GET' => 'GET',
                                'POST' => 'POST',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('changelog_extract')
                            ->label('Changelog extract')
                            ->helperText('Regular expression to extract changelog'),
                        Forms\Components\Checkbox::make('skip_license_check')
                            ->label('Skip license check')
                            ->helperText('Some plugins like WP All Import does not return a valid license key. Only tick this box if you get a \'403 Invalid license\' error'),
                    ]),

                // Conditionally display fields for WPML
                Forms\Components\Section::make('WPML Details')
                    ->statePath('settings')
                    ->visible(function ($get) {
                        return $get('updater') === 'wpml';
                    })
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required(),
                    ]),

                // Conditionally display fields for Woocommerce
                Forms\Components\Section::make('Woocommerce Details')
                    ->statePath('settings')
                    ->visible(function ($get) {
                        return $get('updater') === 'woocommerce';
                    })
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required(),
                    ]),

                // Conditionally display fields for Gravity forms
                Forms\Components\Section::make('Gravity Forms Details')
                    ->statePath('settings')
                    ->visible(function ($get) {
                        return $get('updater') === 'gravity_forms';
                    })
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required(),
                    ]),

                // Conditionally display fields for PuC
                Forms\Components\Section::make('PuC Details')
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
                    ]),

                // Conditionally display fields for Direct
                Forms\Components\Section::make('Direct Details')
                    ->statePath('settings')
                    ->visible(function ($get) {
                        return $get('updater') === 'direct';
                    })
                    ->schema([
                        Forms\Components\TextInput::make('url')
                            ->label('Url')
                            ->required()
                            ->helperText('The direct link to the package. You can use ${{ YOUR_VAR }} as a placeholder for environment variables. Note that the environment variables must be prefixed with the package prefix.'),
                    ]),

                // Conditionally display fields for Admin Columns Pro
                Forms\Components\Section::make('Woocommerce Details')
                    ->statePath('settings')
                    ->visible(function ($get) {
                        return $get('updater') === 'admin_columns_pro';
                    })
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->copyable()
                    ->state(function ($record) {
                        $vendoredName = $record->vendoredName();
                        $parts = explode('/', $vendoredName);

                        return '<span class="text-xs text-gray-400">'.$parts[0].'/<br/></span><span>'.$parts[1].'</span>';
                    })
                    ->html()
                    ->icon('heroicon-o-document-duplicate')
                    ->iconColor('gray')
                    ->copyableState(function ($record) {
                        return 'composer require '.$record->vendoredName();
                    })
                    ->copyMessage(function ($record) {
                        return 'Copied "composer require '.$record->vendoredName().'"';
                    }),
                Tables\Columns\TextColumn::make('updater')
                    ->searchable(),
                Tables\Columns\TextColumn::make('latest_release')
                    ->dateTime(config('app.date_time_format')),
                Tables\Columns\TextColumn::make('latest_version')
                    ->badge()
                    ->color('success'),
            ])
            ->filters([
                // filter by updater
                Tables\Filters\SelectFilter::make('updater')
                    ->options([
                        'edd' => 'Easy Digital Downloads',
                        'wpml' => 'WPML',
                        'woocommerce' => 'WooCommerce',
                        'acf' => 'ACF',
                        'gravity_forms' => 'Gravity Forms',
                        'wp_rocket' => 'WP Rocket',
                        'puc' => 'YahnisElsts Plugin Update Checker',
                        'direct' => 'Direct',
                        'manual' => 'Manual',
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('Download')
                        ->url(function ($record) {
                            return $record->getLatestRelease() ? asset('repo/'.$record->getLatestRelease()->path) : null;
                        })
                        ->visible(function ($record) {
                            return $record->getLatestRelease() !== null;
                        })
                        ->icon('heroicon-o-arrow-down-tray'),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            ReleasesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPackages::route('/'),
            'create' => Pages\CreatePackage::route('/create'),
            'edit' => Pages\EditPackage::route('/{record}/edit'),
        ];
    }
}
