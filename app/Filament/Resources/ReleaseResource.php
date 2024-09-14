<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReleaseResource\Pages;
use App\Models\Release;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;

class ReleaseResource extends Resource
{
    protected static ?string $model = Release::class;

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected static ?string $navigationGroup = 'Packages';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('package_id')
                    ->default(40)
                    ->relationship('package', 'name')
                    ->label('Package')
                    ->required()
                    ->native(false)
                    ->searchable(),
                Forms\Components\TextInput::make('version')
                    ->default('1.0.0')
                    ->label('Version')
                    ->required()
                    ->helperText('E.g. 3.2.2'),
                Forms\Components\Textarea::make('changelog')
                    ->default('1.0.0')
                    ->label('Changelog')
                    ->required(),
                Forms\Components\FileUpload::make('path')
                    ->label('File')
                    ->disk('local')
                    ->storeFiles(false)
                    ->acceptedFileTypes([
                        'application/zip',
                        'x-zip-compressed',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup('package.name')
            ->groups([
                'package.name',
            ])
            ->columns([
                Tables\Columns\TextColumn::make('package.name')
                    ->label('Package')
                    ->sortable()
                    ->searchable()
                    ->size(\Filament\Tables\Columns\TextColumn\TextColumnSize::ExtraSmall),
                Tables\Columns\TextColumn::make('package.slug')
                    ->label('Package Slug')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('version')
                    ->searchable()
                    ->badge()
                    ->color(fn ($record) => $record->isLatest() ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Released')
                    ->dateTime(config('app.date_time_format'))
                    ->sortable(),
            ])
            ->filters([
                // Filter by package
                Tables\Filters\SelectFilter::make('package_id')
                    ->options(fn () => \App\Models\Package::pluck('name', 'id')->toArray())
                    ->label('Package'),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('Download')
                        ->url(fn ($record) => asset('repo/'.$record->path))
                        ->icon('heroicon-o-arrow-down-tray'),
                    Tables\Actions\DeleteAction::make(),
                ])->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReleases::route('/'),
            'create' => Pages\CreateRelease::route('/create'),
            'edit' => Pages\EditRelease::route('/{record}/edit'),
        ];
    }
}
