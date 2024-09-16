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
        $updaters = app()->make('updaters');
        $schema = [
            Forms\Components\TextInput::make('slug')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('updater')
                ->required()
                ->options($updaters->map(fn ($updater) => $updater['name']))
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
        ];

        $updaters->each(function ($updater) use (&$schema) {
            $updaterClass = $updater['class'];
            $updaterSchema = $updaterClass::formSchema();
            if ($updaterSchema) {
                $schema[] = $updaterSchema;
            }
        });

        return $form
            ->schema($schema);
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
                Tables\Filters\SelectFilter::make('updater')
                    ->options(app()->make('updaters')->map(fn ($updater) => $updater['name'])),
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
