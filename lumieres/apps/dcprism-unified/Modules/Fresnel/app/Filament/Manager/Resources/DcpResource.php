<?php

namespace Modules\Fresnel\app\Filament\Manager\Resources;

use Modules\Fresnel\app\Models\Dcp;
use Modules\Fresnel\app\Models\Movie;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Modules\Fresnel\app\Filament\Resources\Dcps\Tables\DcpTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use UnitEnum;
use BackedEnum;

class DcpResource extends Resource
{
    protected static ?string $model = Dcp::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-film';

    protected static ?string $recordTitleAttribute = 'id';
    
    protected static ?string $navigationLabel = 'DCPs';
    
    protected static ?string $modelLabel = 'DCP';
    
    protected static ?string $pluralModelLabel = 'DCPs';
    
    protected static ?int $navigationSort = 3;
    
    protected static string|UnitEnum|null $navigationGroup = 'Gestion Festival';

    /**
     * Configure the table with manager-specific adaptations
     */
    public static function table(Table $table): Table
    {
        // Start with the existing table configuration
        $configuredTable = DcpTable::configure($table);

        // Override record actions for manager-specific functionality
        return $configuredTable
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Voir'),
                    EditAction::make()
                        ->label('Éditer')
                        ->visible(fn (Dcp $record): bool => 
                            static::canEditDcp($record)
                        ),
                    Action::make('validate')
                        ->label('Valider DCP')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Dcp $record): bool => 
                            static::canValidateDcp($record)
                        )
                        ->requiresConfirmation()
                        ->modalHeading('Valider le DCP')
                        ->modalDescription('Êtes-vous sûr de vouloir valider ce DCP ? Cette action marquera le DCP comme prêt pour distribution.')
                        ->action(fn (Dcp $record) => static::validateDcp($record)),
                    Action::make('request_revision')
                        ->label('Demander Révision')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('warning')
                        ->visible(fn (Dcp $record): bool => 
                            static::canRequestRevision($record)
                        )
                        ->form([
                            \Filament\Forms\Components\Textarea::make('revision_notes')
                                ->label('Notes de révision')
                                ->required()
                                ->rows(3)
                                ->helperText('Expliquez les changements nécessaires')
                        ])
                        ->action(function (Dcp $record, array $data) {
                            static::requestRevision($record, $data['revision_notes']);
                        }),
                    Action::make('notify_source')
                        ->label('Notifier Source')
                        ->icon('heroicon-o-envelope')
                        ->color('info')
                        ->visible(fn (Dcp $record): bool => 
                            static::canNotifySource($record)
                        )
                        ->requiresConfirmation()
                        ->modalHeading('Notifier la Source')
                        ->modalDescription('Envoyer une notification à la source concernant l\'état de ce DCP ?')
                        ->action(fn (Dcp $record) => static::notifySource($record)),
                    Action::make('download')
                        ->label('Télécharger')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->visible(fn (Dcp $record): bool => 
                            $record->is_valid && !empty($record->backblaze_file_id)
                        )
                        ->action(function (Dcp $record) {
                            try {
                                $backblazeService = app(\App\Services\BackblazeService::class);
                                return $backblazeService->download($record->movie);
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Erreur de téléchargement')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                ])
            ])
            ->defaultSort('uploaded_at', 'desc')
            ->striped();
    }

    /**
     * Filter DCPs to only show those from manager's festivals
     */
    public static function getEloquentQuery(): Builder
    {
        $festivalId = Session::get('selected_festival_id');
        
        if (!$festivalId) {
            // Si aucun festival sélectionné, retourner une query vide
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }
        
        return parent::getEloquentQuery()
            ->whereHas('movie.festivals', function (Builder $query) use ($festivalId) {
                $query->where('festival_id', $festivalId);
            })
            ->with(['movie', 'version', 'uploader', 'audioLanguage', 'subtitleLanguage']);
    }

    /**
     * Check if manager can edit this DCP
     */
    protected static function canEditDcp(Dcp $dcp): bool
    {
        $user = auth()->user();
        $festivalId = Session::get('selected_festival_id');
        
        if (!$user || !$user->hasRole('manager') || !$festivalId) {
            return false;
        }

        // Vérifier que le DCP appartient à un film du festival du manager
        return $dcp->movie->festivals()->where('festival_id', $festivalId)->exists();
    }

    /**
     * Check if manager can validate this DCP
     */
    protected static function canValidateDcp(Dcp $dcp): bool
    {
        return static::canEditDcp($dcp) && 
               !$dcp->is_valid && 
               in_array($dcp->status, [Dcp::STATUS_UPLOADED, Dcp::STATUS_PROCESSING]);
    }

    /**
     * Check if manager can request revision for this DCP
     */
    protected static function canRequestRevision(Dcp $dcp): bool
    {
        return static::canEditDcp($dcp) && 
               in_array($dcp->status, [Dcp::STATUS_UPLOADED, Dcp::STATUS_PROCESSING, Dcp::STATUS_VALID]);
    }

    /**
     * Check if manager can notify source about this DCP
     */
    protected static function canNotifySource(Dcp $dcp): bool
    {
        return static::canEditDcp($dcp) && !empty($dcp->movie->source_email);
    }

    /**
     * Validate a DCP
     */
    protected static function validateDcp(Dcp $dcp): void
    {
        try {
            $dcp->markAsValid('Validé par le manager du festival');
            
            Notification::make()
                ->title('DCP validé avec succès')
                ->body("Le DCP #{$dcp->id} a été marqué comme valide")
                ->success()
                ->send();

            // Optionnel: Notifier la source automatiquement
            static::notifySource($dcp, 'Votre DCP a été validé avec succès');
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur de validation')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Request revision for a DCP
     */
    protected static function requestRevision(Dcp $dcp, string $notes): void
    {
        try {
            $dcp->update([
                'status' => Dcp::STATUS_INVALID,
                'is_valid' => false,
                'validation_notes' => $notes
            ]);
            
            Notification::make()
                ->title('Révision demandée')
                ->body("Une demande de révision a été envoyée pour le DCP #{$dcp->id}")
                ->warning()
                ->send();

            // Notifier la source
            static::notifySource($dcp, "Révision demandée: {$notes}");
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Notify source about DCP status
     */
    protected static function notifySource(Dcp $dcp, ?string $customMessage = null): void
    {
        try {
            $movie = $dcp->movie;
            
            if (empty($movie->source_email)) {
                throw new \Exception('Aucun email de source configuré pour ce film');
            }

            // Envoi d'email via MailingService
            $mailingService = app(\App\Services\MailingService::class);
            $emailSent = $mailingService->sendDcpStatusUpdate($dcp, $customMessage ?? "Mise à jour du statut de votre DCP #{$dcp->id}");
            
            $message = $customMessage ?? "Mise à jour du statut de votre DCP #{$dcp->id}";
            
            // Pour l'instant, juste une notification dans l'interface
            Notification::make()
                ->title('Source notifiée')
                ->body("Email envoyé à {$movie->source_email}: {$message}")
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur de notification')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => \Modules\Fresnel\app\Filament\Manager\Resources\DcpResource\Pages\ListDcps::route('/'),
        ];
    }
}
