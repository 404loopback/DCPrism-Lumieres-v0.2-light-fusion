<?php

namespace Modules\Fresnel\app\Filament\Tech\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Modules\Fresnel\app\Models\Dcp;
use UnitEnum;

class TechToolsPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Outils Techniques';

    protected static ?string $title = 'Outils de Validation Technique';

    protected static ?int $navigationSort = 2;

    protected static string|UnitEnum|null $navigationGroup = 'Validation Technique';

    protected string $view = 'filament.pages.tech.tech-tools';

    public string $activeTab = 'validation';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()?->hasRole('tech');
    }

    public function mount(): void
    {
        // Initialisation des outils techniques
    }

    protected function getViewData(): array
    {
        return [
            'user' => auth()->user(),
            'toolCategories' => $this->getToolCategories(),
            'validationStats' => $this->getValidationStats(),
            'activeTab' => $this->activeTab,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                // À implémenter : colonnes selon l'onglet actif
            ])
            ->filters([
                // À implémenter : filtres par statut, format, problème
            ])
            ->actions([
                // À implémenter : actions de validation, test, rapport
            ]);
    }

    protected function getTableQuery()
    {
        // Récupérer les DCPs selon le festival de l'utilisateur tech
        $userFestivalIds = auth()->user()?->festivals->pluck('id')->toArray() ?? [];
        
        if (empty($userFestivalIds)) {
            return Dcp::query()->whereRaw('1 = 0');
        }

        return match ($this->activeTab) {
            'validation' => $this->getPendingValidationQuery($userFestivalIds),
            'issues' => $this->getIssuesQuery($userFestivalIds),
            'reports' => $this->getReportsQuery($userFestivalIds),
            'tools' => $this->getToolsQuery(),
            default => $this->getPendingValidationQuery($userFestivalIds),
        };
    }

    public function changeTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    private function getToolCategories(): array
    {
        return [
            'validation' => 'Validation DCPs en attente',
            'issues' => 'Problèmes détectés',
            'reports' => 'Rapports techniques',
            'tools' => 'Outils de diagnostic',
        ];
    }

    private function getValidationStats(): array
    {
        $userFestivalIds = auth()->user()?->festivals->pluck('id')->toArray() ?? [];
        
        if (empty($userFestivalIds)) {
            return [
                'pending' => 0,
                'validated' => 0,
                'issues' => 0,
            ];
        }

        return [
            'pending' => Dcp::whereHas('movie.festivals', function ($query) use ($userFestivalIds) {
                $query->whereIn('festival_id', $userFestivalIds);
            })
            ->where('status', 'uploaded')
            ->count(),
            
            'validated' => Dcp::whereHas('movie.festivals', function ($query) use ($userFestivalIds) {
                $query->whereIn('festival_id', $userFestivalIds);
            })
            ->where('is_valid', true)
            ->count(),
            
            'issues' => Dcp::whereHas('movie.festivals', function ($query) use ($userFestivalIds) {
                $query->whereIn('festival_id', $userFestivalIds);
            })
            ->where('status', 'error')
            ->count(),
        ];
    }

    private function getPendingValidationQuery(array $festivalIds)
    {
        return Dcp::whereHas('movie.festivals', function ($query) use ($festivalIds) {
            $query->whereIn('festival_id', $festivalIds);
        })
        ->where('status', 'uploaded')
        ->where('is_valid', false);
    }

    private function getIssuesQuery(array $festivalIds)
    {
        return Dcp::whereHas('movie.festivals', function ($query) use ($festivalIds) {
            $query->whereIn('festival_id', $festivalIds);
        })
        ->where('status', 'error');
    }

    private function getReportsQuery(array $festivalIds)
    {
        // À implémenter : rapports techniques générés
        return Dcp::query()->whereRaw('1 = 0');
    }

    private function getToolsQuery()
    {
        // À implémenter : liste des outils disponibles
        return Dcp::query()->whereRaw('1 = 0');
    }
}
