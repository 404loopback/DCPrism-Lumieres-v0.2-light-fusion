<?php

namespace Modules\Fresnel\app\Filament\Infrastructure\Resources\Providers;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Fresnel\app\Filament\Infrastructure\Resources\Providers\Pages\ManageProviders;
use Modules\Fresnel\app\Models\Provider;

class ProviderResource extends Resource
{
    protected static ?string $model = Provider::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCloud;

    protected static ?string $navigationLabel = 'Cloud Providers';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->label('Provider Code')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., vultr, aws, azure'),

                Forms\Components\TextInput::make('display_name')
                    ->label('Display Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., Vultr, Amazon Web Services'),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->placeholder('Description of the cloud provider')
                    ->rows(3),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                \Filament\Tables\Columns\TextColumn::make('display_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->wrap()
                    ->limit(50),

                \Filament\Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProviders::route('/'),
        ];
    }
}
