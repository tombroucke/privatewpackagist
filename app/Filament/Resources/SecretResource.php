<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SecretResource\Pages;
use App\Models\Secret;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class SecretResource extends Resource
{
    protected static ?string $model = Secret::class;

    /**
     * The navigation icon for the resource.
     */
    protected static ?string $navigationIcon = 'heroicon-o-key';

    /**
     * The navigation group for the resource.
     */
    protected static ?string $navigationGroup = 'Packages';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Secret name')
                    ->required()
                    ->autofocus()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\TextInput::make('value')
                    ->password()
                    ->revealable()
                    ->required()
                    ->formatStateUsing(fn (?string $state): ?string => filled($state) ? rescue(fn () => Crypt::decryptString($state), $state, false) : $state)
                    ->dehydrateStateUsing(fn (string $state): string => Crypt::encryptString($state))
                    ->dehydrated(fn (?string $state): bool => filled($state)),

                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options(app()->make('secretTypes')
                        ->mapWithKeys(fn ($secret) => [$secret => Str::of($secret)->title()->replace('_', ' ')->__toString()])
                    )
                    ->default('license_key')
                    ->native(false)
                    ->disabledOn('edit')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListSecrets::route('/'),
        ];
    }
}
