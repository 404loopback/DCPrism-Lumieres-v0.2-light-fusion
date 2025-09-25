<?php

namespace Modules\Fresnel\app\Filament\Shared\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Modules\Fresnel\app\Models\User;
use UnitEnum;

class TeamManagementPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Gestion Équipe';

    protected static ?string $title = 'Gestion de l\'Équipe';

    protected static ?int $navigationSort = 5;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected string $view = 'filament.pages.shared.team-management';

    public string $activeTab = 'team';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()?->hasAnyRole([
            'manager', 'tech', 'cinema', 'admin', 'super_admin'
        ]);
    }

    public function mount(): void
    {
        // Initialisation selon le rôle de l'utilisateur
    }

    protected function getViewData(): array
    {
        return [
            'user' => auth()->user(),
            'userRole' => $this->getCurrentUserRole(),
            'teamMembers' => $this->getTeamMembers(),
            'festivalTeams' => $this->getFestivalTeams(),
            'activeTab' => $this->activeTab,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                // À implémenter : colonnes selon le contexte (manager, tech, cinema)
            ])
            ->filters([
                // À implémenter : filtres par rôle, festival, statut
            ])
            ->actions([
                // À implémenter : actions selon les permissions
            ]);
    }

    protected function getTableQuery()
    {
        $user = auth()->user();
        
        // Adapter la requête selon le rôle de l'utilisateur connecté
        if ($user?->hasRole(['admin', 'super_admin'])) {
            // Admin voit tous les utilisateurs
            return User::query();
        }
        
        if ($user?->hasRole('manager')) {
            // Manager voit les utilisateurs de ses festivals
            return User::whereHas('festivals', function ($query) use ($user) {
                $query->whereIn('festivals.id', $user->festivals->pluck('id'));
            });
        }
        
        if ($user?->hasRole(['tech', 'cinema'])) {
            // Tech et Cinema voient leur équipe locale + collaborateurs sur même festival
            return User::whereHas('festivals', function ($query) use ($user) {
                $query->whereIn('festivals.id', $user->festivals->pluck('id'));
            })
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['tech', 'cinema', 'source']);
            });
        }

        return User::whereRaw('1 = 0'); // Aucun accès par défaut
    }

    public function changeTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    private function getCurrentUserRole(): string
    {
        $user = auth()->user();
        if ($user?->hasRole('super_admin')) return 'super_admin';
        if ($user?->hasRole('admin')) return 'admin';
        if ($user?->hasRole('manager')) return 'manager';
        if ($user?->hasRole('tech')) return 'tech';
        if ($user?->hasRole('cinema')) return 'cinema';
        
        return 'unknown';
    }

    private function getTeamMembers(): array
    {
        // À implémenter : récupérer les membres d'équipe selon le contexte
        return [];
    }

    private function getFestivalTeams(): array
    {
        // À implémenter : récupérer les équipes par festival
        return [];
    }
}
