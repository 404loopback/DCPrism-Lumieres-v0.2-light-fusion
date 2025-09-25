<?php

namespace Modules\Fresnel\app\Filament\Builders;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Collection;
use Modules\Fresnel\app\Models\FestivalParameter;
use Modules\Fresnel\app\Models\Parameter;
use Modules\Fresnel\app\Services\Context\FestivalContextService;

/**
 * Centralized builder for creating consistent Filament form fields
 * Replaces repetitive field creation code across Resources
 */
class FormFieldBuilder
{
    public function __construct(
        private FestivalContextService $festivalContext
    ) {}

    /**
     * Build parameter fields for the current festival
     */
    public function buildParameterFields(array $festivalIds = []): array
    {
        // Use provided festival IDs or fall back to current context
        if (empty($festivalIds)) {
            $festivalId = $this->festivalContext->getCurrentFestivalId();
            $festivalIds = $festivalId ? [$festivalId] : [];
        }

        if (empty($festivalIds)) {
            return [
                $this->createNoFestivalPlaceholder(),
            ];
        }

        // Get active parameters for the primary festival
        $festivalParameters = $this->getFestivalParameters($festivalIds[0]);

        if ($festivalParameters->isEmpty()) {
            return [
                $this->createNoParametersPlaceholder(),
            ];
        }

        return $this->buildFieldsFromParameters($festivalParameters);
    }

    /**
     * Build metadata parameter fields
     */
    public function buildMetadataFields(): array
    {
        $festivalId = $this->festivalContext->getCurrentFestivalId();

        if (! $festivalId) {
            return [
                $this->createNoFestivalPlaceholder(),
            ];
        }

        // Get metadata parameters for the festival
        $metadataParameters = $this->getMetadataParameters($festivalId);

        if ($metadataParameters->isEmpty()) {
            return [
                Placeholder::make('no_metadata_parameters')
                    ->content('No metadata parameters configured for this festival.')
                    ->columnSpanFull(),
            ];
        }

        return $this->buildFieldsFromParameters($metadataParameters);
    }

    /**
     * Build a field for a specific parameter
     * Can return Select, TextInput, Toggle, DatePicker, or Textarea
     */
    public function buildParameterField(Parameter $parameter, mixed $defaultValue = null, array $options = []): Select|TextInput|Toggle|DatePicker|Textarea
    {
        $fieldName = $options['field_name'] ?? "parameter_{$parameter->id}";
        $required = $options['required'] ?? (bool) ($parameter->is_required ?? false);

        // Handle special case: AUDIO_LANG uses langs table
        if ($parameter->code === 'AUDIO_LANG') {
            return $this->buildAudioLanguageField($parameter, $fieldName, $required, $defaultValue);
        }
        
        // Handle special case: SUBTITLES uses langs table and conditional logic
        if ($parameter->code === 'SUBTITLES') {
            return $this->buildSubtitleLanguageField($parameter, $fieldName, $required, $defaultValue);
        }
        
        // Handle parameters with predefined values
        if (! empty($parameter->possible_values)) {
            return $this->buildSelectField($parameter, $fieldName, $required, $defaultValue);
        }

        // Handle different parameter types
        return match ($parameter->type) {
            Parameter::TYPE_INT => $this->buildNumericField($parameter, $fieldName, $required, $defaultValue),
            Parameter::TYPE_FLOAT => $this->buildFloatField($parameter, $fieldName, $required, $defaultValue),
            Parameter::TYPE_BOOL => $this->buildToggleField($parameter, $fieldName, $required, $defaultValue),
            Parameter::TYPE_DATE => $this->buildDateField($parameter, $fieldName, $required, $defaultValue),
            Parameter::TYPE_JSON => $this->buildJsonField($parameter, $fieldName, $required, $defaultValue),
            default => $this->buildTextInputField($parameter, $fieldName, $required, $defaultValue)
        };
    }

    /**
     * Build audio language field with conditional logic based on VERSION_TYPE
     */
    private function buildAudioLanguageField(Parameter $parameter, string $fieldName, bool $required, mixed $defaultValue): Select
    {
        // Récupérer les langues depuis la table langs
        $langs = \DB::table('langs')
            ->orderBy('iso_639_1')
            ->get()
            ->mapWithKeys(function ($lang) {
                // Utiliser le nom français s'il existe, sinon le nom anglais
                $displayName = $lang->french_name ?: $lang->name;
                // Format: "FR - Français" ou "EN - English"
                $label = $lang->iso_639_1 . ' - ' . $displayName;
                // La valeur stockée reste le code ISO
                return [$lang->iso_639_1 => $label];
            })
            ->toArray();

        $versionTypeFieldName = $this->findVersionTypeFieldName();

        return Select::make($fieldName)
            ->label($parameter->name)
            ->options($langs)
            ->required($required)
            ->default($defaultValue)
            ->searchable()
            ->live(onBlur: true)
            ->disabled(function ($get) use ($versionTypeFieldName) {
                // Désactiver si VERSION_TYPE = 'MUTE'
                return $versionTypeFieldName && $get($versionTypeFieldName) === 'MUTE';
            })
            ->helperText($parameter->description . ' - Codes ISO 639-1 (Désactivé pour les versions muettes)');
    }

    /**
     * Build subtitle language field with conditional visibility based on VERSION_TYPE
     */
    private function buildSubtitleLanguageField(Parameter $parameter, string $fieldName, bool $required, mixed $defaultValue): Select
    {
        // Récupérer les langues depuis la table langs
        $langs = \DB::table('langs')
            ->orderBy('iso_639_1')
            ->get()
            ->mapWithKeys(function ($lang) {
                // Utiliser le nom français s'il existe, sinon le nom anglais
                $displayName = $lang->french_name ?: $lang->name;
                // Format: "FR - Français" ou "EN - English"
                $label = $lang->iso_639_1 . ' - ' . $displayName;
                // La valeur stockée reste le code ISO
                return [$lang->iso_639_1 => $label];
            })
            ->toArray();

        $versionTypeFieldName = $this->findVersionTypeFieldName();

        return Select::make($fieldName)
            ->label($parameter->name)
            ->options($langs)
            ->required($required)
            ->default($defaultValue)
            ->searchable()
            ->live(onBlur: true)
            ->visible(function ($get) use ($versionTypeFieldName) {
                // Visible uniquement si VERSION_TYPE est VOST, DUBST ou MUTE
                if (!$versionTypeFieldName) {
                    return true; // Si pas de VERSION_TYPE trouvé, on affiche par défaut
                }
                
                $versionType = $get($versionTypeFieldName);
                return in_array($versionType, ['VOST', 'DUBST', 'MUTE']);
            })
            ->helperText($parameter->description . ' - Codes ISO 639-1 (Visible pour VOST, DUBST, MUTE)');
    }

    /**
     * Find the field name for VERSION_TYPE parameter
     */
    private function findVersionTypeFieldName(): ?string
    {
        $festivalId = $this->festivalContext->getCurrentFestivalId();
        
        if (!$festivalId) {
            return null;
        }

        // Chercher le paramètre VERSION_TYPE dans les paramètres du festival
        $versionTypeParameter = FestivalParameter::where('festival_id', $festivalId)
            ->where('is_enabled', true)
            ->whereHas('parameter', function ($query) {
                $query->where('code', 'VERSION_TYPE')
                    ->where('is_active', true);
            })
            ->with('parameter')
            ->first();

        if (!$versionTypeParameter) {
            return null;
        }

        return "parameter_{$versionTypeParameter->parameter->id}";
    }

    /**
     * Build a select field for languages using langs table
     */
    private function buildLanguageSelectField(Parameter $parameter, string $fieldName, bool $required, mixed $defaultValue): Select
    {
        // Récupérer les langues depuis la table langs
        $langs = \DB::table('langs')
            ->orderBy('iso_639_1')
            ->get()
            ->mapWithKeys(function ($lang) {
                // Utiliser le nom français s'il existe, sinon le nom anglais
                $displayName = $lang->french_name ?: $lang->name;
                // Format: "FR - Français" ou "EN - English"
                $label = $lang->iso_639_1 . ' - ' . $displayName;
                // La valeur stockée reste le code ISO
                return [$lang->iso_639_1 => $label];
            })
            ->toArray();

        return Select::make($fieldName)
            ->label($parameter->name)
            ->options($langs)
            ->required($required)
            ->default($defaultValue)
            ->searchable() // Pour pouvoir chercher dans la liste
            ->live(onBlur: true) // Pour déclencher la mise à jour de nomenclature
            ->helperText($parameter->description . ' - Codes ISO 639-1');
    }

    /**
     * Build a select field with predefined options
     */
    private function buildSelectField(Parameter $parameter, string $fieldName, bool $required, mixed $defaultValue): Select
    {
        $options = array_combine($parameter->possible_values, $parameter->possible_values);

        return Select::make($fieldName)
            ->label($parameter->name)
            ->options($options)
            ->required($required)
            ->default($defaultValue)
            ->live(onBlur: true) // Pour déclencher la mise à jour de nomenclature
            ->helperText($parameter->description);
    }

    /**
     * Build a numeric input field
     */
    private function buildNumericField(Parameter $parameter, string $fieldName, bool $required, mixed $defaultValue): TextInput
    {
        return TextInput::make($fieldName)
            ->label($parameter->name)
            ->numeric()
            ->required($required)
            ->default($defaultValue)
            ->live(onBlur: true) // Pour déclencher la mise à jour de nomenclature
            ->helperText($parameter->description);
    }

    /**
     * Build a float input field
     */
    private function buildFloatField(Parameter $parameter, string $fieldName, bool $required, mixed $defaultValue): TextInput
    {
        return TextInput::make($fieldName)
            ->label($parameter->name)
            ->numeric()
            ->step(0.01)
            ->required($required)
            ->default($defaultValue)
            ->live(onBlur: true) // Pour déclencher la mise à jour de nomenclature
            ->helperText($parameter->description);
    }

    /**
     * Build a toggle field for boolean parameters
     */
    private function buildToggleField(Parameter $parameter, string $fieldName, bool $required, mixed $defaultValue): Toggle
    {
        return Toggle::make($fieldName)
            ->label($parameter->name)
            ->required($required)
            ->default($defaultValue)
            ->live() // Toggle change immédiatement
            ->helperText($parameter->description);
    }

    /**
     * Build a date picker field
     */
    private function buildDateField(Parameter $parameter, string $fieldName, bool $required, mixed $defaultValue): DatePicker
    {
        return DatePicker::make($fieldName)
            ->label($parameter->name)
            ->required($required)
            ->default($defaultValue)
            ->live() // Pour déclencher la mise à jour de nomenclature
            ->helperText($parameter->description);
    }

    /**
     * Build a JSON textarea field
     */
    private function buildJsonField(Parameter $parameter, string $fieldName, bool $required, mixed $defaultValue): Textarea
    {
        return Textarea::make($fieldName)
            ->label($parameter->name)
            ->rows(3)
            ->required($required)
            ->default($defaultValue)
            ->live(onBlur: true) // Pour déclencher la mise à jour de nomenclature
            ->helperText($parameter->description.' (JSON format)');
    }

    /**
     * Build a text input field (default for strings)
     * Returns either TextInput or Textarea (both extend Component)
     */
    private function buildTextInputField(Parameter $parameter, string $fieldName, bool $required, mixed $defaultValue): TextInput|Textarea
    {
        // Use textarea for long descriptions or parameters with "description" in name
        $shouldUseTextarea = str_contains(strtolower($parameter->name), 'description') ||
                           str_contains(strtolower($parameter->description ?? ''), 'description') ||
                           strlen($parameter->name) > 50;

        if ($shouldUseTextarea) {
            return Textarea::make($fieldName)
                ->label($parameter->name)
                ->rows(3)
                ->required($required)
                ->default($defaultValue)
                ->live(onBlur: true) // Pour déclencher la mise à jour de nomenclature
                ->helperText($parameter->description);
        }

        return TextInput::make($fieldName)
            ->label($parameter->name)
            ->required($required)
            ->default($defaultValue)
            ->live(onBlur: true) // Pour déclencher la mise à jour de nomenclature
            ->helperText($parameter->description);
    }

    /**
     * Get festival parameters for a specific festival
     */
    private function getFestivalParameters(int $festivalId): Collection
    {
        return FestivalParameter::where('festival_id', $festivalId)
            ->where('is_enabled', true)
            ->with('parameter')
            ->ordered()
            ->get()
            ->filter(fn ($fp) => $fp->parameter && $fp->parameter->is_active);
    }

    /**
     * Get metadata parameters for a festival
     */
    private function getMetadataParameters(int $festivalId): Collection
    {
        return FestivalParameter::where('festival_id', $festivalId)
            ->where('is_enabled', true)
            ->whereHas('parameter', function ($query) {
                $query->where('category', 'metadata')
                    ->where('is_active', true);
            })
            ->with('parameter')
            ->ordered()
            ->get();
    }

    /**
     * Build fields from a collection of festival parameters
     */
    private function buildFieldsFromParameters(Collection $festivalParameters): array
    {
        $fields = [];

        foreach ($festivalParameters as $festivalParameter) {
            $parameter = $festivalParameter->parameter;
            
            $defaultValue = $festivalParameter->getEffectiveDefaultValue();
            $required = (bool) ($parameter->is_required ?? false);

            // Les paramètres titre sont maintenant gérés directement dans le wizard
            // On les ignore ici pour éviter les doublons
            if ($this->isTitleParameter($parameter)) {
                continue; // Ignore les paramètres titre
            }

            $field = $this->buildParameterField($parameter, $defaultValue, [
                'required' => $required,
                'field_name' => "parameter_{$parameter->id}",
            ]);

            // Add festival-specific notes if present
            if (! empty($festivalParameter->festival_specific_notes)) {
                $existingHelperText = $parameter->description ?? '';
                $field->helperText($existingHelperText.' - Note: '.$festivalParameter->festival_specific_notes);
            }

            $fields[] = $field;
        }

        return $fields;
    }

    /**
     * Create placeholder for when no festival is selected
     */
    private function createNoFestivalPlaceholder(): Placeholder
    {
        return Placeholder::make('no_festival_selected')
            ->content('Please select a festival to see available parameters.')
            ->columnSpanFull();
    }

    /**
     * Create placeholder for when no parameters are configured
     */
    private function createNoParametersPlaceholder(): Placeholder
    {
        return Placeholder::make('no_parameters')
            ->content('No parameters configured for this festival. Please configure festival parameters first.')
            ->columnSpanFull();
    }


    /**
     * Check if a parameter is the title parameter (system field)
     * Le titre est déjà géré dans le step 1, on l'exclut du step 2
     */
    private function isTitleParameter(Parameter $parameter): bool
    {
        // Vérifier par nom de paramètre (différentes variations possibles)
        $titleNames = ['title', 'titre', 'film_title', 'movie_title', 'nom', 'name'];
        $paramName = strtolower($parameter->name);
        $paramCode = strtolower($parameter->code ?? '');
        
        foreach ($titleNames as $titleName) {
            if (str_contains($paramName, $titleName) || str_contains($paramCode, $titleName)) {
                return true;
            }
        }
        
        // Vérifier par catégorie système si disponible
        if ($parameter->is_system && str_contains(strtolower($parameter->category ?? ''), 'title')) {
            return true;
        }
        
        return false;
    }
}
