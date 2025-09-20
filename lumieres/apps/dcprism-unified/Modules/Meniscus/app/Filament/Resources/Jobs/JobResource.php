<?php

namespace Modules\Meniscus\app\Filament\Resources\Jobs;

use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Meniscus\app\Filament\Resources\Jobs\Pages\CreateJob;
use Modules\Meniscus\app\Filament\Resources\Jobs\Pages\EditJob;
use Modules\Meniscus\app\Filament\Resources\Jobs\Pages\ListJobs;
use Modules\Meniscus\app\Models\Job;

class JobResource extends Resource
{
    protected static ?string $model = Job::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFilm;

    protected static ?string $navigationLabel = 'DCP Jobs';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('Job Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Job Title'),
                        Forms\Components\Textarea::make('description')
                            ->placeholder('Job description...')
                            ->rows(3),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required(),
                        Forms\Components\TextInput::make('progress')
                            ->numeric()
                            ->default(0)
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100),
                    ])->columns(2),

                Forms\Components\Section::make('File Information')
                    ->schema([
                        Forms\Components\TextInput::make('source_file_path')
                            ->label('Source File Path')
                            ->placeholder('/path/to/source/file.mov'),
                        Forms\Components\TextInput::make('source_file_size')
                            ->label('Source File Size')
                            ->placeholder('1.2 GB'),
                        Forms\Components\TextInput::make('output_path')
                            ->label('Output Path')
                            ->placeholder('/path/to/output/'),
                        Forms\Components\Select::make('format')
                            ->options([
                                '2K' => '2K (2048x1080)',
                                '4K' => '4K (4096x2160)',
                                'HD' => 'HD (1920x1080)',
                            ])
                            ->default('2K'),
                    ])->columns(2),

                Forms\Components\Section::make('Processing')
                    ->schema([
                        Forms\Components\TextInput::make('worker_id')
                            ->label('Worker ID')
                            ->numeric(),
                        Forms\Components\DateTimePicker::make('started_at')
                            ->label('Started At'),
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('Completed At'),
                        Forms\Components\DateTimePicker::make('estimated_completion')
                            ->label('Estimated Completion'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold),
                \Filament\Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'failed',
                        'warning' => 'pending',
                        'primary' => 'processing',
                        'success' => 'completed',
                        'gray' => 'cancelled',
                    ]),
                \Filament\Tables\Columns\TextColumn::make('progress')
                    ->suffix('%')
                    ->alignCenter()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('format')
                    ->badge()
                    ->color('info'),
                \Filament\Tables\Columns\TextColumn::make('worker_id')
                    ->label('Worker')
                    ->formatStateUsing(fn ($state) => $state ? "Worker #{$state}" : 'Unassigned')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                \Filament\Tables\Columns\TextColumn::make('source_file_size')
                    ->label('Size')
                    ->toggleable(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('format')
                    ->options([
                        '2K' => '2K',
                        '4K' => '4K',
                        'HD' => 'HD',
                    ]),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\Action::make('retry')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (Job $record) => in_array($record->status, ['failed', 'cancelled']))
                    ->requiresConfirmation()
                    ->action(fn (Job $record) => $record->update(['status' => 'pending', 'progress' => 0])),
                \Filament\Actions\Action::make('cancel')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (Job $record) => in_array($record->status, ['pending', 'processing']))
                    ->requiresConfirmation()
                    ->action(fn (Job $record) => $record->update(['status' => 'cancelled'])),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => ListJobs::route('/'),
            'create' => CreateJob::route('/create'),
            'edit' => EditJob::route('/{record}/edit'),
        ];
    }
}
