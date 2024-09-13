<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackageResource\Pages;
use App\Filament\Resources\PackageResource\RelationManagers\ReleasesRelationManager;
use App\Models\Package;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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
                        'edd' => 'Easy Digital Downloads',
                        'wpml' => 'WPML',
                        'woocommerce' => 'WooCommerce',
                        'acf' => 'ACF',
                        'gravity_forms' => 'Gravity Forms',
                        'wp_rocket' => 'WP Rocket',
                        'puc' => 'YahnisElsts Plugin Update Checker',
                    ])
                    ->reactive()
                    ->afterStateUpdated(function (callable $set, $state) {
                        $set('updater', $state);
                    }),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'wordpress-plugin' => 'WordPress Plugin',
                        'wordpress-theme' => 'WordPress Theme',
                    ])
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('updater')
                    ->searchable(),
                Tables\Columns\TextColumn::make('latest_release')
                    ->searchable()
                    ->dateTime(config('app.date_time_format')),
                Tables\Columns\TextColumn::make('latest_version')
                    ->searchable()
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
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
