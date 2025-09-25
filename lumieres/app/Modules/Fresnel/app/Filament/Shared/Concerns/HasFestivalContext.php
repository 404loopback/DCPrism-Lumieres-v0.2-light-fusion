<?php

namespace Modules\Fresnel\app\Filament\Shared\Concerns;

use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Modules\Fresnel\app\Models\Festival;

/**
 * Trait for managing festival context in Filament components
 * Provides common methods for festival selection and filtering
 */
trait HasFestivalContext
{
    protected ?Festival $selectedFestival = null;

    /**
     * Get the currently selected festival
     */
    public function getSelectedFestival(): ?Festival
    {
        if (! $this->selectedFestival) {
            $this->selectedFestival = $this->loadDefaultFestival();
        }

        return $this->selectedFestival;
    }

    /**
     * Set the selected festival
     */
    public function setSelectedFestival(?Festival $festival): void
    {
        $this->selectedFestival = $festival;
        $this->onFestivalChanged($festival);
    }

    /**
     * Load the default festival (can be overridden)
     */
    protected function loadDefaultFestival(): ?Festival
    {
        // Get from session if available
        if ($festivalId = session('selected_festival_id')) {
            $festival = Festival::find($festivalId);
            if ($festival) {
                return $festival;
            }
        }

        // Get user's first accessible festival
        $user = auth()->user();
        if ($user && method_exists($user, 'festivals')) {
            return $user->festivals()->first();
        }

        // Fallback to first active festival
        return Festival::where('is_active', true)->first();
    }

    /**
     * Get festival selector form component
     */
    protected function getFestivalSelector(): Select
    {
        return Select::make('festival_id')
            ->label('Festival')
            ->options(function () {
                $user = auth()->user();

                if ($user && method_exists($user, 'festivals')) {
                    // User has specific festivals assigned
                    return $user->festivals()
                        ->where('is_active', true)
                        ->pluck('name', 'id')
                        ->toArray();
                }

                // Admin or user with all festival access
                return Festival::where('is_active', true)
                    ->pluck('name', 'id')
                    ->toArray();
            })
            ->default(function () {
                $festival = $this->getSelectedFestival();

                return $festival ? $festival->id : null;
            })
            ->reactive()
            ->afterStateUpdated(function ($state) {
                if ($state) {
                    $festival = Festival::find($state);
                    $this->setSelectedFestival($festival);

                    // Store in session for persistence
                    session(['selected_festival_id' => $state]);
                }
            });
    }

    /**
     * Apply festival filter to query builder
     */
    protected function applyFestivalFilter(Builder $query, ?int $festivalId = null): Builder
    {
        $festivalId = $festivalId ?? $this->getSelectedFestival()?->id;

        if (! $festivalId) {
            return $query;
        }

        $modelClass = get_class($query->getModel());

        // Check if the model has a direct festival relationship
        if (method_exists($query->getModel(), 'festivals')) {
            return $query->whereHas('festivals', function ($q) use ($festivalId) {
                $q->where('festivals.id', $festivalId);
            });
        }

        // Check if the model has a festival_id foreign key
        if (in_array('festival_id', $query->getModel()->getFillable())) {
            return $query->where('festival_id', $festivalId);
        }

        // Special case for DCP model - filter through movie.festivals
        if (str_contains($modelClass, 'Dcp')) {
            return $query->whereHas('movie.festivals', function ($q) use ($festivalId) {
                $q->where('festivals.id', $festivalId);
            });
        }

        // Special case for Version model - filter through movie.festivals
        if (str_contains($modelClass, 'Version')) {
            return $query->whereHas('movie.festivals', function ($q) use ($festivalId) {
                $q->where('festivals.id', $festivalId);
            });
        }

        return $query;
    }

    /**
     * Get festival-aware query for widgets/resources
     */
    protected function getFestivalAwareQuery(string $modelClass): Builder
    {
        $query = $modelClass::query();

        return $this->applyFestivalFilter($query);
    }

    /**
     * Hook called when festival changes (can be overridden)
     */
    protected function onFestivalChanged(?Festival $festival): void
    {
        // Override in child classes for specific behavior
    }

    /**
     * Get festival context for statistics and data
     */
    protected function getFestivalContext(): array
    {
        $festival = $this->getSelectedFestival();

        if (! $festival) {
            return [
                'festival' => null,
                'name' => 'Tous les festivals',
                'has_filter' => false,
            ];
        }

        return [
            'festival' => $festival,
            'name' => $festival->name,
            'id' => $festival->id,
            'has_filter' => true,
            'is_active' => $festival->is_active,
            'start_date' => $festival->start_date,
            'end_date' => $festival->end_date,
        ];
    }

    /**
     * Check if user has access to festival
     */
    protected function canAccessFestival(Festival $festival): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Admin et super_admin ont accès à tous les festivals
        if ($user->hasAnyRole(['admin', 'super_admin'])) {
            return true;
        }

        // Check if user has explicit access to this festival
        if (method_exists($user, 'festivals')) {
            return $user->festivals()->where('festivals.id', $festival->id)->exists();
        }

        return false;
    }

    /**
     * Get accessible festivals for current user
     */
    protected function getAccessibleFestivals(): \Illuminate\Database\Eloquent\Collection
    {
        $user = auth()->user();

        if (! $user) {
            return collect();
        }

        // Admin et super_admin ont accès à tous les festivals
        if ($user->hasAnyRole(['admin', 'super_admin'])) {
            return Festival::where('is_active', true)->get();
        }

        // User-specific festivals
        if (method_exists($user, 'festivals')) {
            return $user->festivals()->where('is_active', true)->get();
        }

        return collect();
    }

    /**
     * Check if a festival is selected
     */
    protected function hasFestivalSelected(): bool
    {
        return $this->getSelectedFestival() !== null;
    }

    /**
     * Create a statistic helper method compatible with widgets
     */
    protected function createStatistic(
        string $label,
        int|string $value,
        ?string $description = null,
        ?string $icon = null,
        string $color = 'primary',
        ?array $chart = null
    ) {
        // This method will be available in widgets that use this trait
        if (method_exists($this, 'createStat')) {
            return $this->createStat($label, $value, $description, $icon, $color, $chart);
        }

        // Fallback for non-widget contexts
        return compact('label', 'value', 'description', 'icon', 'color', 'chart');
    }
}
