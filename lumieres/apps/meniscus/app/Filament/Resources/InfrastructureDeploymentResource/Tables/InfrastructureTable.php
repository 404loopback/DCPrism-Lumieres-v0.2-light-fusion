<?php

namespace App\Filament\Resources\InfrastructureDeploymentResource\Tables;

use App\Models\InfrastructureDeployment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InfrastructureTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('scenario')
                    ->label('Scenario')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        InfrastructureDeployment::SCENARIO_BACKEND_AUTOMATION => 'Backend Automation',
                        InfrastructureDeployment::SCENARIO_MANUAL_TESTING => 'Manual Testing',
                        default => $state,
                    }),

                TextColumn::make('environment')
                    ->badge(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('-', ' ', $state))),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('scenario')
                    ->options([
                        InfrastructureDeployment::SCENARIO_BACKEND_AUTOMATION => 'Backend Automation',
                        InfrastructureDeployment::SCENARIO_MANUAL_TESTING => 'Manual Testing',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        InfrastructureDeployment::STATUS_DRAFT => 'Draft',
                        InfrastructureDeployment::STATUS_DEPLOYING => 'Deploying',
                        InfrastructureDeployment::STATUS_DEPLOYED => 'Deployed',
                        InfrastructureDeployment::STATUS_FAILED => 'Failed',
                        InfrastructureDeployment::STATUS_DESTROYED => 'Destroyed',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
