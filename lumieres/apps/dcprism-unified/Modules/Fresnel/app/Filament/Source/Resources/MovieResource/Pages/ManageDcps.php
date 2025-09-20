<?php

namespace Modules\Fresnel\app\Filament\Source\Resources\MovieResource\Pages;

use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Storage;
use Modules\Fresnel\app\Filament\Source\Resources\MovieResource;
use Modules\Fresnel\app\Models\Dcp;
use Modules\Fresnel\app\Models\Lang;
use Modules\Fresnel\app\Models\Movie;
use Modules\Fresnel\app\Models\Version;

class ManageDcps extends Page
{
    protected static string $resource = MovieResource::class;

    protected string $view = 'filament.source.pages.manage-dcps';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cloud-arrow-up';

    public Movie $record;

    public ?array $data = [];

    public function mount(Movie $record): void
    {
        $this->record = $record;

        // Vérifier que la Source a accès à ce film
        if ($record->source_email !== auth()->user()->email) {
            abort(403, 'Accès refusé à ce film');
        }
    }

    public function getTitle(): string|Htmlable
    {
        return "Gestion DCPs - {$this->record->title}";
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Uploadez les versions DCP demandées pour ce film';
    }

    /**
     * Obtenir les versions attendues pour ce film
     */
    public function getExpectedVersions(): array
    {
        $expectedVersions = $this->record->expected_versions ?? [];

        if (empty($expectedVersions)) {
            return [];
        }

        return array_map(function ($versionType) {
            return [
                'type' => $versionType,
                'label' => $this->getVersionLabel($versionType),
                'existing_version' => $this->record->versions()
                    ->where('type', $versionType)
                    ->first(),
            ];
        }, $expectedVersions);
    }

    /**
     * Obtenir le libellé d'un type de version
     */
    private function getVersionLabel(string $type): string
    {
        $labels = [
            'VO' => 'Version Originale',
            'VOST' => 'VO Sous-titrée',
            'VF' => 'Version Française',
            'VOSTF' => 'VO Sous-titrée Français',
            'DUB' => 'Version Doublée',
        ];

        return $labels[$type] ?? $type;
    }

    /**
     * Créer une version pour ce film
     */
    public function createVersionAction(): Actions\Action
    {
        return Actions\Action::make('createVersion')
            ->label('Créer une Version')
            ->icon('heroicon-o-plus')
            ->color('success')
            ->form([
                Section::make('Nouvelle Version DCP')
                    ->schema([
                        Select::make('type')
                            ->label('Type de Version')
                            ->options(array_combine(
                                $this->record->expected_versions ?? [],
                                array_map([$this, 'getVersionLabel'], $this->record->expected_versions ?? [])
                            ))
                            ->required()
                            ->native(false),

                        Select::make('audio_lang')
                            ->label('Langue Audio')
                            ->options(Lang::pluck('name', 'iso_639_1'))
                            ->searchable()
                            ->required(),

                        Select::make('sub_lang')
                            ->label('Langue Sous-titres')
                            ->options(Lang::pluck('name', 'iso_639_1'))
                            ->searchable()
                            ->placeholder('Aucune'),

                        Select::make('accessibility')
                            ->label('Accessibilité')
                            ->options([
                                'HI' => 'Malentendants (HI)',
                                'VI' => 'Malvoyants (VI)',
                                'AD' => 'Audio Description (AD)',
                                'CC' => 'Closed Captions (CC)',
                            ])
                            ->multiple()
                            ->placeholder('Aucune'),
                    ]),
            ])
            ->action(function (array $data) {
                // Vérifier que la version n'existe pas déjà
                $existingVersion = $this->record->versions()
                    ->where('type', $data['type'])
                    ->first();

                if ($existingVersion) {
                    Notification::make()
                        ->title('Version déjà existante')
                        ->body("Une version {$data['type']} existe déjà pour ce film")
                        ->warning()
                        ->send();

                    return;
                }

                // Créer la version
                $version = $this->record->versions()->create([
                    'type' => $data['type'],
                    'audio_lang' => $data['audio_lang'],
                    'sub_lang' => $data['sub_lang'] ?? null,
                    'accessibility' => is_array($data['accessibility']) ? implode(',', $data['accessibility']) : $data['accessibility'],
                ]);

                Notification::make()
                    ->title('Version créée avec succès')
                    ->body("Version {$data['type']} créée. Vous pouvez maintenant uploader le DCP.")
                    ->success()
                    ->send();

                return redirect()->to(request()->url());
            });
    }

    /**
     * Uploader un DCP pour une version
     */
    public function uploadDcpAction(): Actions\Action
    {
        return Actions\Action::make('uploadDcp')
            ->label('Upload DCP')
            ->icon('heroicon-o-cloud-arrow-up')
            ->color('primary')
            ->form([
                Section::make('Upload DCP')
                    ->schema([
                        Select::make('version_id')
                            ->label('Version')
                            ->options(
                                $this->record->versions()
                                    ->get()
                                    ->pluck('generated_nomenclature', 'id')
                            )
                            ->required()
                            ->native(false),

                        FileUpload::make('dcp_file')
                            ->label('Fichier DCP')
                            ->acceptedFileTypes(['application/zip', 'application/x-tar'])
                            ->maxSize(50 * 1024 * 1024) // 50GB max
                            ->disk('backblaze') // Configuré pour Backblaze
                            ->directory(fn () => "dcps/{$this->record->id}")
                            ->preserveFilenames()
                            ->required()
                            ->helperText('Formats acceptés : ZIP, TAR. Taille max : 50GB'),

                        TextInput::make('notes')
                            ->label('Notes (optionnel)')
                            ->placeholder('Commentaires sur ce DCP...')
                            ->columnSpanFull(),
                    ]),
            ])
            ->action(function (array $data) {
                $version = Version::find($data['version_id']);

                if (! $version || $version->movie_id !== $this->record->id) {
                    Notification::make()
                        ->title('Erreur')
                        ->body('Version invalide')
                        ->danger()
                        ->send();

                    return;
                }

                // Créer le DCP
                $dcp = $version->dcps()->create([
                    'movie_id' => $this->record->id,
                    'uploaded_by' => auth()->id(),
                    'file_path' => $data['dcp_file'],
                    'file_size' => Storage::disk('backblaze')->size($data['dcp_file']),
                    'status' => Dcp::STATUS_UPLOADED,
                    'uploaded_at' => now(),
                    'validation_notes' => $data['notes'] ?? null,
                    'is_ov' => $version->type === 'VO',
                    'audio_lang' => $version->audio_lang,
                    'subtitle_lang' => $version->sub_lang,
                ]);

                // Mettre à jour le statut du film
                if ($this->record->status === Movie::STATUS_PENDING) {
                    $this->record->update(['status' => Movie::STATUS_UPLOADING]);
                }

                Notification::make()
                    ->title('DCP uploadé avec succès')
                    ->body("Le DCP pour la version {$version->type} a été uploadé et est en cours de traitement.")
                    ->success()
                    ->send();

                return redirect()->to(request()->url());
            });
    }

    /**
     * Obtenir les versions existantes avec leurs DCPs
     */
    public function getVersionsWithDcps()
    {
        return $this->record->versions()
            ->with(['dcps' => function ($query) {
                $query->latest('created_at');
            }])
            ->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->createVersionAction(),
            $this->uploadDcpAction(),
        ];
    }
}
