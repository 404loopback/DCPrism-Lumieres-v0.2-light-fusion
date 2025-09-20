<?php

namespace Modules\Fresnel\app\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Modules\Fresnel\app\Models\User;
use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Models\Lang;
use Modules\Fresnel\app\Models\Nomenclature;
use Modules\Fresnel\app\Models\Parameter;
use Modules\Fresnel\app\Filament\Resources\Users\Tables\UserTable;
use Modules\Fresnel\app\Filament\Resources\Festivals\Tables\FestivalTable;
use Modules\Fresnel\app\Filament\Resources\Langs\Tables\LangTable;
use Modules\Fresnel\app\Filament\Resources\Nomenclatures\Tables\NomenclatureTable;
use Modules\Fresnel\app\Filament\Resources\Parameters\Tables\ParameterTable;
use Livewire\Attributes\On;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;
use BezhanSalleh\FilamentShield\Resources\RoleResource\Tables\RoleTable;

class AdministrationPage extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationLabel = 'Administration';
    
    protected static ?string $title = 'Administration';
    
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.administration';
    
    public string $activeTab = 'users';
    
    public int $tableKey = 0;
    
    /**
     * Table des Utilisateurs
     */
    public function usersTable(Table $table): Table
    {
        return UserTable::configure(
            $table->query(User::query())
        );
    }
    
    /**
     * Table des Festivals
     */
    public function festivalsTable(Table $table): Table
    {
        return FestivalTable::configure(
            $table->query(Festival::query())
        );
    }
    
    /**
     * Table des Langues
     */
    public function langsTable(Table $table): Table
    {
        return LangTable::configure(
            $table->query(Lang::query())
        );
    }
    
    /**
     * Table des Nomenclatures
     */
    public function nomenclaturesTable(Table $table): Table
    {
        return NomenclatureTable::configure(
            $table->query(Nomenclature::query())
        );
    }
    
    /**
     * Table des Paramètres
     */
    public function parametersTable(Table $table): Table
    {
        return ParameterTable::configure(
            $table->query(Parameter::query())
        );
    }
    
    /**
     * Table des Rôles (Shield)
     */
    public function rolesTable(Table $table): Table
    {
        return $table
            ->query(Role::query())
            ->columns([
                TextColumn::make('name')
                    ->label('Nom du rôle')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions')
                    ->badge(),
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Utilisateurs')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Gérer permissions')
                    ->icon('heroicon-o-shield-check')
                    ->url(fn (Role $record) => '/fresnel/admin/shield/roles/' . $record->id . '/edit'),
                Action::make('view')
                    ->label('Voir détails')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Role $record) => '/fresnel/admin/shield/roles/' . $record->id),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Nouveau rôle')
                    ->icon('heroicon-o-plus')
                    ->url('/fresnel/admin/shield/roles/create'),
                Action::make('shield_dashboard')
                    ->label('Interface Shield complète')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url('/fresnel/admin/shield/roles')
                    ->openUrlInNewTab(),
            ]);
    }
    
    /**
     * Changer d'onglet
     */
    public function changeTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->tableKey++; // Force le re-render
        
        // Réinitialiser complètement la table
        $this->resetTable();
        
        // Force le refresh du composant
        $this->dispatch('$refresh');
    }
    
    /**
     * Récupérer la table active
     */
    public function table(Table $table): Table
    {
        return match($this->activeTab) {
            'users' => $this->usersTable($table),
            'festivals' => $this->festivalsTable($table),
            'langs' => $this->langsTable($table),
            'nomenclatures' => $this->nomenclaturesTable($table),
            'parameters' => $this->parametersTable($table),
            'roles' => $this->rolesTable($table),
            default => $this->usersTable($table),
        };
    }
}
