<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecipeResource\Pages;
use App\Models\Recipe;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RecipeResource extends Resource
{
    /**
     * The resource class this resource belongs to.
     */
    protected static ?string $model = Recipe::class;

    /**
     * The navigation icon for the resource.
     */
    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    /**
     * The navigation group for the resource.
     */
    protected static ?string $navigationGroup = 'Packages';

    /**
     * The navigation sort order for the resource.
     */
    protected static ?int $navigationSort = 3;

    /**
     * The form for the resource.
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    /**
     * The table for the resource.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable(),

                Tables\Columns\TextColumn::make('packages')
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-o-archive-box')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('options')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('secrets')
                    ->badge()
                    ->color('danger')
                    ->icon('heroicon-o-key')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
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
            'index' => Pages\ListRecipes::route('/'),
        ];
    }
}
