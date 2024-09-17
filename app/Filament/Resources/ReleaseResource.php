<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReleaseResource\Pages;
use App\Models\Package;
use App\Models\Release;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Table;

class ReleaseResource extends Resource
{
    /**
     * The resource class this resource belongs to.
     */
    protected static ?string $model = Release::class;

    /**
     * The navigation icon for the resource.
     */
    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    /**
     * The navigation group for the resource.
     */
    protected static ?string $navigationGroup = 'Packages';

    /**
     * The form for the resource.
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('package_id')
                    ->relationship('package', 'name', function ($query, $record) {
                        if (! $record || ($record->exists && $record->package->recipe === 'manual')) {
                            $query->where('recipe', 'manual');
                        }

                        $query->orderBy('name');

                        return $query;
                    })
                    ->label('Package')
                    ->prefixIcon('heroicon-o-archive-box')
                    ->required()
                    ->preload()
                    ->disabled(fn ($record) => $record && $record->exists && $record->package->recipe !== 'manual')
                    ->searchable(),

                Forms\Components\TextInput::make('version')
                    ->label('Version')
                    ->required()
                    ->prefix('v')
                    ->placeholder('1.0.0')
                    ->disabled(fn ($record) => $record && $record->exists && $record->package->recipe !== 'manual'),

                Forms\Components\RichEditor::make('changelog')
                    ->label('Changelog')
                    ->required()
                    ->hidden(fn ($state, $operation) => $operation !== 'create' && blank($state))
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('path')
                    ->columnSpanFull()
                    ->label('Release Archive')
                    ->disk('local')
                    ->disabled(fn ($record) => $record && $record->exists && $record->package->recipe !== 'manual')
                    ->storeFiles(false)
                    ->acceptedFileTypes([
                        'application/zip',
                        'x-zip-compressed',
                    ]),
            ]);
    }

    /**
     * The table for the resource.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultGroup('package.name')
            ->groups(['package.name'])
            ->columns([
                Tables\Columns\TextColumn::make('package.name')
                    ->label('Package')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-archive-box')
                    ->size(TextColumnSize::ExtraSmall),

                Tables\Columns\TextColumn::make('package.slug')
                    ->label('Slug')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('version')
                    ->searchable()
                    ->badge()
                    ->color(fn ($record) => $record->isLatest() ? 'success' : 'warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Released')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('package_id')
                    ->options(fn () => Package::pluck('name', 'id')->toArray())
                    ->label('Package'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->hiddenLabel(),

                ActionGroup::make([
                    Action::make('Download')
                        ->url(fn ($record) => asset('repo/'.$record->path))
                        ->icon('heroicon-o-arrow-down-tray'),

                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * The relation managers for the resource.
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * The pages for the resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReleases::route('/'),
        ];
    }
}
