<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReleaseResource\Pages;
use App\Models\Release;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('package.name')
                    ->label('Package')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('package.slug')
                    ->label('Package Slug')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('version')
                    ->searchable(),
            ])
            ->filters([
                // Filter by package
                Tables\Filters\SelectFilter::make('package_id')
                    ->options(fn () => \App\Models\Package::pluck('name', 'id')->toArray())
                    ->label('Package'),
            ])
            ->actions([
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
