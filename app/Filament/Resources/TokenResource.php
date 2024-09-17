<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TokenResource\Pages;
use App\Models\Token;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Table;

class TokenResource extends Resource
{
    /**
     * The resource class this resource belongs to.
     */
    protected static ?string $model = Token::class;

    /**
     * The navigation icon for the resource.
     */
    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    /**
     * The navigation group for the resource.
     */
    protected static ?string $navigationGroup = 'Access';

    /**
     * The form for the resource.
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('token')
                    ->required()
                    ->default(fn () => bin2hex(random_bytes(16)))
                    ->maxLength(255)
                    ->disabled(fn ($record) => $record),
            ]);
    }

    /**
     * The table for the resource.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->icon('heroicon-o-document-duplicate')
                    ->iconColor('gray')
                    ->iconPosition(IconPosition::After)
                    ->copyable()
                    ->copyMessage('Username copied'),

                Tables\Columns\TextColumn::make('token')
                    ->searchable()
                    ->icon('heroicon-o-document-duplicate')
                    ->iconColor('gray')
                    ->iconPosition(IconPosition::After)
                    ->copyable()
                    ->copyMessage('Token copied'),

                Tables\Columns\TextColumn::make('last_used_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTokens::route('/'),
        ];
    }
}
