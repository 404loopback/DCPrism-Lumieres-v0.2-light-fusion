<?php

namespace Modules\Fresnel\app\Filament\Shared\Forms;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Shared form field components to reduce duplication
 * across Filament panels and maintain consistency
 */
class Fields
{
    /**
     * Standard title field with consistent validation
     */
    public static function title(string $label = 'Titre', bool $required = true): TextInput
    {
        return TextInput::make('title')
            ->label($label)
            ->required($required)
            ->maxLength(255);
    }

    /**
     * Standard name field for entities
     */
    public static function name(string $label = 'Nom', bool $required = true, ?string $placeholder = null): TextInput
    {
        $field = TextInput::make('name')
            ->label($label)
            ->required($required)
            ->maxLength(255);

        if ($placeholder) {
            $field->placeholder($placeholder);
        }

        return $field;
    }

    /**
     * Standard email field with validation
     */
    public static function email(string $label = 'Email', bool $required = true, bool $unique = false, ?string $placeholder = null): TextInput
    {
        $field = TextInput::make('email')
            ->label($label)
            ->email()
            ->required($required)
            ->maxLength(255);

        if ($unique) {
            $field->unique(ignoreRecord: true);
        }

        if ($placeholder) {
            $field->placeholder($placeholder);
        }

        return $field;
    }

    /**
     * Standard description textarea
     */
    public static function description(string $label = 'Description', int $rows = 3, bool $required = false): Textarea
    {
        return Textarea::make('description')
            ->label($label)
            ->rows($rows)
            ->required($required)
            ->columnSpanFull();
    }

    /**
     * Standard status select field
     */
    public static function status(string $label = 'Statut', array $options = [], $default = null, bool $required = true): Select
    {
        $field = Select::make('status')
            ->label($label)
            ->required($required);

        if (! empty($options)) {
            $field->options($options);
        }

        if ($default !== null) {
            $field->default($default);
        }

        return $field;
    }

    /**
     * Standard year field with validation
     */
    public static function year(string $label = 'Année', bool $required = false): TextInput
    {
        return TextInput::make('year')
            ->label($label)
            ->numeric()
            ->required($required)
            ->minValue(1900)
            ->maxValue(date('Y') + 5);
    }

    /**
     * Standard country field
     */
    public static function country(string $label = 'Pays', bool $required = false): TextInput
    {
        return TextInput::make('country')
            ->label($label)
            ->required($required)
            ->maxLength(100);
    }

    /**
     * Standard language field
     */
    public static function language(string $label = 'Langue', bool $required = false): TextInput
    {
        return TextInput::make('language')
            ->label($label)
            ->required($required)
            ->maxLength(50);
    }

    /**
     * Standard duration field with minutes suffix
     */
    public static function duration(string $label = 'Durée', string $suffix = 'min'): TextInput
    {
        return TextInput::make('duration')
            ->label($label)
            ->numeric()
            ->suffix($suffix);
    }

    /**
     * Standard phone field
     */
    public static function phone(string $label = 'Téléphone', bool $required = false): TextInput
    {
        return TextInput::make('contact_phone')
            ->label($label)
            ->required($required)
            ->maxLength(50);
    }

    /**
     * Standard website URL field
     */
    public static function website(string $label = 'Site web', bool $required = false): TextInput
    {
        return TextInput::make('website')
            ->label($label)
            ->url()
            ->required($required)
            ->maxLength(255);
    }

    /**
     * Standard active toggle
     */
    public static function isActive(string $label = 'Actif', ?string $helperText = null, bool $default = true): Toggle
    {
        $field = Toggle::make('is_active')
            ->label($label)
            ->default($default);

        if ($helperText) {
            $field->helperText($helperText);
        }

        return $field;
    }

    /**
     * Standard password field with confirmation
     */
    public static function password(string $label = 'Mot de passe', bool $isEdit = false): array
    {
        return [
            TextInput::make('password')
                ->label($label)
                ->password()
                ->required(fn (string $context): bool => $context === 'create')
                ->dehydrated(fn ($state) => filled($state))
                ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                ->rule(Password::default())
                ->placeholder(fn (string $context) => $context === 'edit'
                        ? 'Laissez vide pour conserver le mot de passe actuel'
                        : 'Minimum 8 caractères'
                ),

            TextInput::make('password_confirmation')
                ->label('Confirmer le mot de passe')
                ->password()
                ->required(fn (string $context): bool => $context === 'create')
                ->dehydrated(false)
                ->same('password')
                ->placeholder(fn (string $context) => $context === 'edit'
                        ? 'Confirmez le nouveau mot de passe si modifié'
                        : 'Confirmez le mot de passe'
                ),
        ];
    }

    /**
     * Standard date picker with Brussels timezone
     */
    public static function datePicker(
        string $fieldName,
        string $label,
        bool $required = false,
        string $format = 'd/m/Y',
        ?string $afterOrEqual = null
    ): DatePicker {
        $field = DatePicker::make($fieldName)
            ->label($label)
            ->required($required)
            ->displayFormat($format);

        if ($afterOrEqual) {
            $field->afterOrEqual($afterOrEqual);
        }

        return $field;
    }

    /**
     * Standard file upload for DCP files
     */
    public static function dcpFileUpload(
        string $label = 'Fichier DCP',
        string $directory = 'dcps',
        int $maxSizeGB = 50
    ): FileUpload {
        return FileUpload::make('file_path')
            ->label($label)
            ->disk('local')
            ->directory($directory)
            ->acceptedFileTypes(['application/zip', 'application/x-tar'])
            ->maxSize($maxSizeGB * 1024 * 1024) // Convert GB to KB
            ->helperText('Téléchargez le fichier DCP (ZIP ou TAR)')
            ->columnSpanFull();
    }

    /**
     * Standard Backblaze folder field
     */
    public static function backblazeFolder(string $label = 'Dossier Backblaze', ?string $helperText = null): TextInput
    {
        $field = TextInput::make('backblaze_folder')
            ->label($label)
            ->maxLength(255);

        if ($helperText) {
            $field->helperText($helperText);
        }

        return $field;
    }

    /**
     * Standard numeric field with suffix
     */
    public static function numericWithSuffix(
        string $fieldName,
        string $label,
        string $suffix,
        bool $required = false,
        ?string $helperText = null
    ): TextInput {
        $field = TextInput::make($fieldName)
            ->label($label)
            ->numeric()
            ->required($required)
            ->suffix($suffix);

        if ($helperText) {
            $field->helperText($helperText);
        }

        return $field;
    }

    /**
     * Shield/Spatie roles selection field
     * Compatible avec le système de rôles Shield
     */
    public static function shieldRoleSelect(
        string $label = 'Rôles', 
        bool $multiple = false, 
        bool $searchable = true
    ): Select {
        return Select::make('roles')
            ->label($label)
            ->relationship('roles', 'name')
            ->multiple($multiple)
            ->searchable($searchable)
            ->preload()
            ->required()
            ->placeholder('Sélectionnez un ou plusieurs rôles')
            ->helperText(fn (callable $get) => 
                $multiple 
                    ? 'Un utilisateur peut avoir plusieurs rôles. Sélectionnez tous les rôles applicables.'
                    : 'Sélectionnez le rôle principal de cet utilisateur.'
            );
    }
}
