<?php

namespace Modules\Fresnel\app\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Modules\Fresnel\app\Models\User;
use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Models\Lang;
use Modules\Fresnel\app\Models\Nomenclature;
use Modules\Fresnel\app\Models\Parameter;
use Modules\Fresnel\app\Filament\Resources\Users\Tables\UsersTable;
use Modules\Fresnel\app\Filament\Resources\Festivals\Tables\FestivalsTable;
use Modules\Fresnel\app\Filament\Resources\Langs\Tables\LangsTable;
use Modules\Fresnel\app\Filament\Resources\Nomenclatures\Tables\NomenclaturesTable;
use Modules\Fresnel\app\Filament\Resources\Parameters\Tables\ParametersTable;
use Livewire\Attributes\On;
use Illuminate\Database\Eloquent\Builder;

class AdministrationPage extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationLabel = 'Administration';
    
    protected static ?string $title = 'Administration';
    
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.administration';
    
    public string $activeTab = 'users';
    
    /**
     * Table des Utilisateurs
     */
    public function usersTable(Table $table): Table
    {
        return UsersTable::configure(
            $table->query(User::query())
        );
    }
    
    /**
     * Table des Festivals
     */
    public function festivalsTable(Table $table): Table
    {
        return FestivalsTable::configure(
            $table->query(Festival::query())
        );
    }
    
    /**
     * Table des Langues
     */
    public function langsTable(Table $table): Table
    {
        return LangsTable::configure(
            $table->query(Lang::query())
        );
    }
    
    /**
     * Table des Nomenclatures
     */
    public function nomenclaturesTable(Table $table): Table
    {
        return NomenclaturesTable::configure(
            $table->query(Nomenclature::query())
        );
    }
    
    /**
     * Table des Paramètres
     */
    public function parametersTable(Table $table): Table
    {
        return ParametersTable::configure(
            $table->query(Parameter::query())
        );
    }
    
    /**
     * Changer d'onglet
     */
    public function changeTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
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
            default => $this->usersTable($table),
        };
    }
}
