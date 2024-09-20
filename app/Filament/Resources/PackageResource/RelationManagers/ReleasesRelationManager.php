<?php

namespace App\Filament\Resources\PackageResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class ReleasesRelationManager extends RelationManager
{
    /**
     * The relationship that the manager is responsible for.
     */
    protected static string $relationship = 'releases';

    /**
     * Listeners for the relation manager.
     */
    protected $listeners = ['refreshRelation' => '$refresh'];

    /**
     * The form for the relation manager.
     */
    public function form(Form $form): Form
    {
        return $form;
    }

    /**
     * The table for the relation manager.
     */
    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordTitleAttribute('name')
            ->columns([
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
                    ->dateTime(config('packagist.date_time_format'))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),

                Action::make('Download')
                    ->url(fn ($record) => asset('repo/'.$record->path))
                    ->icon('heroicon-o-arrow-down-tray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
