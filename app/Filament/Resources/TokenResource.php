<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TokenResource\Pages;
use App\Filament\Resources\TokenResource\RelationManagers;
use App\Models\Token;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
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

                Forms\Components\Toggle::make('deactivated_at')
                    ->formatStateUsing(function ($record) {
                        return ! $record || $record->isActive();
                    })
                    ->label('Active'),
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

                Tables\Columns\TextColumn::make('activity.created_at')
                    ->label('Last Used At')
                    ->dateTime()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        $lastActivity = $record->activity()->where('action', 'authenticate')->latest()->first();

                        return $lastActivity->created_at ?? null;
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\ToggleColumn::make('deactivated_at')
                    ->label('Active')
                    ->state(function (Token $token): bool {
                        return $token->isActive();
                    })
                    ->afterStateUpdated(function ($record, $state) {
                        Notification::make()
                            ->icon(function () use ($state) {
                                return $state ? 'heroicon-o-check-circle' : 'heroicon-o-information-circle';
                            })
                            ->iconColor(function () use ($state) {
                                return $state ? 'success' : 'info';
                            })
                            ->title($state ? 'Token activated.' : 'Token deactivated.')
                            ->send();
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->hiddenLabel(),

                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
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
            RelationManagers\TokenActivityRelationManager::class,
        ];
    }

    /**
     * The pages for the resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTokens::route('/'),
            'view' => Pages\ViewToken::route('/{record}/view'),
        ];
    }
}
