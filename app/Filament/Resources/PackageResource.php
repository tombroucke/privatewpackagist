<?php

namespace App\Filament\Resources;

use App\Events\RecipeFormsCollectedEvent;
use App\Filament\Resources\PackageResource\Pages;
use App\Filament\Resources\PackageResource\RelationManagers\ReleasesRelationManager;
use App\Models\Package;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class PackageResource extends Resource
{
    /**
     * The resource class this resource belongs to.
     */
    protected static ?string $model = Package::class;

    /**
     * The navigation icon for the resource.
     */
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    /**
     * The navigation group for the resource.
     */
    protected static ?string $navigationGroup = 'Packages';

    /**
     * The package recipes.
     */
    protected static ?Collection $recipes = null;

    /**
     * Retrieve the package recipes.
     */
    public static function getRecipes(): Collection
    {
        return self::$recipes ??= app()->make('recipes');
    }

    /**
     * The form for the resource.
     */
    public static function form(Form $form): Form
    {
        $schema = [
            Forms\Components\TextInput::make('slug')
                ->label('Package name')
                ->prefix(config('packagist.vendor').'/')
                ->required()
                ->autofocus()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            Forms\Components\Select::make('type')
                ->prefixIcon('heroicon-o-folder')
                ->required()
                ->options([
                    'wordpress-plugin' => 'WordPress Plugin',
                    'wordpress-muplugin' => 'WordPress MU Plugin',
                    'wordpress-theme' => 'WordPress Theme',
                ])
                ->searchable()
                ->default('wordpress-plugin'),

            Forms\Components\Select::make('recipe')
                ->prefixIcon('heroicon-o-light-bulb')
                ->required()
                ->options(self::getRecipes()->mapWithKeys(fn ($recipe) => [
                    $recipe::slug() => $recipe::name(),
                ]))
                ->reactive()
                ->searchable()
                ->disabled(fn ($operation) => $operation !== 'create'),
        ];
        foreach (self::getRecipes() as $recipe) {

            $options = collect($recipe::forms());
            event(new RecipeFormsCollectedEvent($options, $recipe));

            $secrets = collect($recipe::secrets())
                ->map(function ($secretType) {
                    return Forms\Components\Select::make('secrets.'.$secretType)
                        ->label(Str::of($secretType)->title()->replace('_', ' '))
                        ->relationship(
                            name: 'secrets',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query) => $query->where('type', $secretType),
                        )
                        ->native(false)
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm(function () use ($secretType) {
                            return [
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

                                Forms\Components\TextInput::make('type') // Hidden input to save the value
                                    ->readOnly()
                                    ->default($secretType), // Ensure it has the correct default value
                            ];
                        });
                });

            if ($options->isNotEmpty()) {
                $options = [
                    Forms\Components\Fieldset::make('Options')
                        ->statePath('settings')
                        ->schema($options->all()),
                ];
            }

            if ($secrets->isNotEmpty()) {
                $secrets = [
                    Forms\Components\Fieldset::make('Secrets')
                        ->statePath('secrets')
                        ->schema($secrets->all()),
                ];
            }

            if (count($options) > 0 && count($secrets) > 0) {
                $schema[] = Forms\Components\Section::make("{$recipe::name()} Details")
                    ->icon('heroicon-o-cog-6-tooth')
                    ->description('Configure the package settings.')
                    ->visible(fn ($get) => $get('recipe') === $recipe::slug())
                    ->columns(2)
                    ->schema([
                        ...$options,
                        ...$secrets,
                    ]);
            }
        }

        return $form
            ->schema($schema);
    }

    /**
     * The table for the resource.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\IconColumn::make('valid')
                    ->label('License')
                    ->icon(fn ($record) => $record->valid ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn ($record) => $record->valid ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->copyable()
                    ->state(fn ($record) => new HtmlString(
                        sprintf('<code class="bg-gray-100 dark:bg-gray-800 py-1 px-1.5 text-xs rounded shadow">%s</code>', $record->vendoredName())
                    ))
                    ->icon('heroicon-o-document-duplicate')
                    ->iconColor('gray')
                    ->copyableState(fn ($record) => "composer require {$record->vendoredName()}")
                    ->copyMessage(fn ($record) => "Copied `composer require {$record->vendoredName()}`"),

                Tables\Columns\TextColumn::make('latest_version')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('latest_release')
                    ->dateTimeTooltip(),

                Tables\Columns\TextColumn::make('recipe')
                    ->badge()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('recipe')
                    ->options(self::getRecipes()->mapWithkeys(fn ($recipe) => [$recipe::slug() => $recipe::name()])),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('Download')
                        ->url(fn ($record) => $record->getLatestRelease() ? asset('repo/'.$record->getLatestRelease()->path) : null)
                        ->visible(fn ($record) => $record->getLatestRelease() !== null)
                        ->icon('heroicon-o-arrow-down-tray'),

                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->iconButton(),
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
            ReleasesRelationManager::class,
        ];
    }

    /**
     * The pages for the resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPackages::route('/'),
            'edit' => Pages\EditPackage::route('/{record}/edit'),
        ];
    }
}
