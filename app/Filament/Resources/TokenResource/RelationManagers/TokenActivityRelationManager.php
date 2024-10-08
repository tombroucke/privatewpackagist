<?php

namespace App\Filament\Resources\TokenResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TokenActivityRelationManager extends RelationManager
{
    protected static string $relationship = 'activity';

    public function form(Form $form): Form
    {
        return $form;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('action')
                    ->searchable()
                    ->badge()
                    ->color(fn ($record) => match ($record->action) {
                        'activate' => 'success',
                        'deactivate' => 'gray',
                        'authenticate_blocked' => 'danger',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('message'),
                Tables\Columns\TextColumn::make('ip_address'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime(config('packagist.date_time_format'))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
