<?php

namespace Modules\Fresnel\app\Filament\Manager\Pages;

use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Fresnel\app\Models\FestivalParameter;
use Modules\Fresnel\app\Models\Parameter;
use UnitEnum;

class SelectParameters extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-plus-circle';

    protected string $view = 'fresnel::filament.manager.pages.select-parameters';

    protected static ?string $title = 'Ajouter des Paramètres';

    protected static string|UnitEnum|null $navigationGroup = 'Configuration Festival';

    protected static ?int $navigationSort = 4;

    protected static bool $shouldRegisterNavigation = false; // Masquer de la navigation

    protected function getTableQuery(): Builder
    {
        $festivalId = auth()->user()->current_festival_id ?? 1;

        // Récupérer les paramètres déjà assignés au festival
        $existingParameterIds = FestivalParameter::where('festival_id', $festivalId)
            ->pluck('parameter_id')
            ->toArray();

        // Retourner les paramètres disponibles - exclure les paramètres système car ils sont ajoutés automatiquement
        return Parameter::availableForFestivals()
            ->whereNotIn('id', $existingParameterIds)
            ->where('is_system', false)
            ->orderBy('category')
            ->orderBy('name');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                IconColumn::make('icon')
                    ->label('')
                    ->icon(fn (Parameter $record): string => $record->icon ? "heroicon-o-{$record->icon}" : 'heroicon-o-cog')
                    ->color(fn (Parameter $record): string => $record->color ?? 'gray')
                    ->tooltip(fn (Parameter $record): string => $record->short_description ?? $record->name)
                    ->width('40px'),
                
                IconColumn::make('is_system')
                    ->label('')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-lock-closed' : 'heroicon-o-plus-circle')
                    ->color(fn (bool $state): string => $state ? 'warning' : 'success')
                    ->tooltip(fn (Parameter $record): string => $record->is_system
                            ? 'Paramètre système (ajouté automatiquement)'
                            : 'Paramètre optionnel (peut être ajouté manuellement)'
                    )
                    ->width('30px'),

                TextColumn::make('name')
                    ->label('Paramètre')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->color(fn (Parameter $record): string => $record->is_system ? 'warning' : 'primary'
                    ),

                TextColumn::make('category')
                    ->label('Catégorie')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'technical' => 'primary',
                        'video' => 'success',
                        'audio' => 'warning',
                        'subtitle' => 'danger',
                        'content' => 'gray',
                        'metadata' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                TextColumn::make('short_description')
                    ->label('Description')
                    ->limit(60)
                    ->tooltip(function (Parameter $record): ?string {
                        if ($record->detailed_description) {
                            $tooltip = $record->detailed_description;
                            if ($record->example_value) {
                                $tooltip .= "\n\n✨ Exemple: {$record->example_value}";
                            }
                            return $tooltip;
                        }
                        return $record->description;
                    })
                    ->placeholder('Aucune description')
                    ->wrap(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Parameter::getAvailableTypes()[$state] ?? $state),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->getStateUsing(function (Parameter $record): string {
                        if ($record->is_system) {
                            return 'Système';
                        }

                        return 'Optionnel';
                    })
                    ->color(function (Parameter $record): string {
                        if ($record->is_system) {
                            return 'danger';
                        }

                        return 'success';
                    }),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options(Parameter::getAvailableCategories())
                    ->placeholder('Toutes les catégories'),

                TernaryFilter::make('is_system')
                    ->label('Paramètres système')
                    ->placeholder('Tous')
                    ->trueLabel('Paramètres système uniquement')
                    ->falseLabel('Paramètres non-système uniquement'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('add_to_festival')
                        ->label('Ajouter au Festival')
                        ->icon('heroicon-o-plus-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $this->addParametersToFestival($records);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->modalHeading('Ajouter les paramètres au festival')
                        ->modalDescription('Voulez-vous ajouter les paramètres sélectionnés à votre festival ?')
                        ->modalSubmitActionLabel('Ajouter')
                        ->disabled(fn (Collection $records): bool => $records->filter(fn ($record) => $record->is_system)->isNotEmpty()
                        ),
                ]),
            ])
            ->recordClasses(fn (Parameter $record) => $record->is_system ? 'bg-yellow-50 dark:bg-yellow-950/20' : null
            )
            ->selectCurrentPageOnly()
            ->defaultSort('category')
            ->emptyStateHeading('Aucun paramètre disponible')
            ->emptyStateDescription('Tous les paramètres ont déjà été ajoutés à votre festival.')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    protected function addParametersToFestival(Collection $records): void
    {
        $festivalId = auth()->user()->current_festival_id ?? 1;
        $addedCount = 0;
        $skippedSystemCount = 0;
        $alreadyExistsCount = 0;

        foreach ($records as $parameter) {
            // Ne pas permettre l'ajout de paramètres système
            if ($parameter->is_system) {
                $skippedSystemCount++;

                continue;
            }

            // Vérifier si le paramètre n'est pas déjà assigné
            $exists = FestivalParameter::where('festival_id', $festivalId)
                ->where('parameter_id', $parameter->id)
                ->exists();

            if ($exists) {
                $alreadyExistsCount++;

                continue;
            }

            FestivalParameter::create([
                'festival_id' => $festivalId,
                'parameter_id' => $parameter->id,
                'is_enabled' => true,
                'display_order' => 0,
            ]);
            $addedCount++;
        }

        // Messages de notification adaptés
        if ($addedCount > 0) {
            $message = "{$addedCount} paramètre(s) ont été ajoutés au festival.";

            if ($skippedSystemCount > 0) {
                $message .= " {$skippedSystemCount} paramètre(s) système ont été ignorés.";
            }

            if ($alreadyExistsCount > 0) {
                $message .= " {$alreadyExistsCount} paramètre(s) existaient déjà.";
            }

            Notification::make()
                ->title('Paramètres ajoutés avec succès')
                ->body($message)
                ->success()
                ->send();
        } else {
            $message = "Aucun paramètre n'a été ajouté.";

            if ($skippedSystemCount > 0) {
                $message .= " {$skippedSystemCount} paramètre(s) système ne peuvent pas être ajoutés manuellement.";
            }

            if ($alreadyExistsCount > 0) {
                $message .= " {$alreadyExistsCount} paramètre(s) existaient déjà.";
            }

            Notification::make()
                ->title('Aucune modification')
                ->body($message)
                ->warning()
                ->send();
        }

        // Rafraîchir la table
        $this->resetTable();
    }

    public function getTitle(): string
    {
        return 'Ajouter des Paramètres Globaux';
    }

    public function getSubheading(): ?string
    {
        return 'Tous les paramètres sont globaux et créés par les administrateurs. Sélectionnez ceux que vous souhaitez utiliser pour votre festival. Les paramètres obligatoires et système sont ajoutés automatiquement à tous les festivals.';
    }
}
