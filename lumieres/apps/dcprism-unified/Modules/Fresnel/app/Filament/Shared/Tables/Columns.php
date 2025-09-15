<?php

namespace Modules\Fresnel\app\Filament\Shared\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;

/**
 * Shared table column components to reduce duplication
 * across Filament panels and maintain consistency
 */
class Columns
{
    /**
     * Standard name/title column with search and sort
     */
    public static function name(string $field = 'name', string $label = 'Nom', int $limit = null): TextColumn
    {
        $column = TextColumn::make($field)
            ->label($label)
            ->searchable()
            ->sortable();
            
        if ($limit) {
            $column->limit($limit);
        }
        
        return $column;
    }

    /**
     * Standard title column with search and sort
     */
    public static function title(string $label = 'Titre', int $limit = 50): TextColumn
    {
        return TextColumn::make('title')
            ->label($label)
            ->searchable()
            ->sortable()
            ->limit($limit);
    }

    /**
     * Standard email column with search, sort and copy
     */
    public static function email(string $field = 'email', string $label = 'Email', int $limit = null): TextColumn
    {
        $column = TextColumn::make($field)
            ->label($label)
            ->searchable()
            ->sortable()
            ->copyable();
            
        if ($limit) {
            $column->limit($limit);
        }
        
        return $column;
    }

    /**
     * Email column with verification status icon inline
     */
    public static function emailWithVerification(string $label = 'Email'): TextColumn
    {
        return TextColumn::make('email')
            ->label($label)
            ->searchable()
            ->sortable()
            ->copyable()
            ->formatStateUsing(function ($state, $record) {
                $isVerified = !is_null($record->email_verified_at);
                $icon = $isVerified 
                    ? '<span class="text-green-600 font-bold ml-2" title="Email vérifié">✓</span>'
                    : '<span class="text-red-600 font-bold ml-2" title="Email non vérifié">✗</span>';
                
                return new \Illuminate\Support\HtmlString($state . $icon);
            })
            ->html()
            ->tooltip(function ($record) {
                return $record->email_verified_at 
                    ? 'Email vérifié le ' . $record->email_verified_at->format('d/m/Y à H:i')
                    : 'Email non vérifié';
            });
    }

    /**
     * Standard status badge column with customizable colors
     */
    public static function statusBadge(
        string $field = 'status',
        string $label = 'Statut',
        array $colors = [],
        array $formatMapping = [],
        bool $sortable = true
    ): BadgeColumn {
        $column = BadgeColumn::make($field)
            ->label($label);
            
        if (!empty($colors)) {
            $column->colors($colors);
        }
        
        if (!empty($formatMapping)) {
            $column->formatStateUsing(fn (string $state): string => $formatMapping[$state] ?? $state);
        }
        
        if ($sortable) {
            $column->sortable();
        }
        
        return $column;
    }

    /**
     * Standard role badge column using Shield roles instead of legacy role column
     */
    public static function roleBadge(string $label = 'Rôle'): TextColumn
    {
        return TextColumn::make('roles.name')
            ->label($label)
            ->badge()
            ->color(fn ($state): string => match ($state) {
                'admin' => 'danger',
                'super_admin' => 'purple',
                'tech' => 'warning', 
                'manager' => 'success',
                'supervisor' => 'info',
                'source' => 'primary',
                'cinema' => 'gray',
                default => 'gray',
            })
            ->formatStateUsing(fn ($state): string => match ($state) {
                'admin' => 'Administrateur',
                'super_admin' => 'Super Admin',
                'tech' => 'Technique',
                'manager' => 'Manager',
                'supervisor' => 'Superviseur',
                'source' => 'Source',
                'cinema' => 'Cinéma',
                default => ucfirst($state),
            })
            ->placeholder('Aucun rôle')
            ->listWithLineBreaks()
            ->limitList(2)
            ->expandableLimitedList();
    }

    /**
     * Standard active/inactive status badge
     */
    public static function activeBadge(string $field = 'is_active', string $label = 'Statut'): BadgeColumn
    {
        return BadgeColumn::make($field)
            ->label($label)
            ->formatStateUsing(fn (bool $state): string => $state ? 'Actif' : 'Inactif')
            ->colors([
                'success' => true,
                'danger' => false,
            ]);
    }

    /**
     * Standard boolean icon column
     */
    public static function booleanIcon(
        string $field,
        string $label,
        string $trueIcon = 'heroicon-o-check-circle',
        string $falseIcon = 'heroicon-o-x-circle',
        string $trueColor = 'success',
        string $falseColor = 'danger',
        callable $tooltip = null
    ): IconColumn {
        $column = IconColumn::make($field)
            ->label($label)
            ->boolean()
            ->trueIcon($trueIcon)
            ->falseIcon($falseIcon)
            ->trueColor($trueColor)
            ->falseColor($falseColor)
            ->alignCenter();
            
        if ($tooltip) {
            $column->tooltip($tooltip);
        }
        
        return $column;
    }

    /**
     * Email verification status icon
     */
    public static function emailVerificationIcon(string $label = 'Email vérifié'): IconColumn
    {
        return static::booleanIcon(
            'email_verified_at',
            $label,
            tooltip: function ($record) {
                return $record->email_verified_at 
                    ? 'Vérifié le ' . $record->email_verified_at->format('d/m/Y à H:i')
                    : 'Email non vérifié';
            }
        );
    }

    /**
     * Standard toggle column for active status
     */
    public static function activeToggle(
        string $field = 'is_active',
        string $label = 'Actif',
        string $onColor = 'success',
        string $offColor = 'danger'
    ): ToggleColumn {
        return ToggleColumn::make($field)
            ->label($label)
            ->onColor($onColor)
            ->offColor($offColor);
    }

    /**
     * Standard count badge column
     */
    public static function countBadge(
        string $relationship,
        string $label,
        string $color = 'primary',
        bool $alignCenter = true
    ): TextColumn {
        $column = TextColumn::make($relationship . '_count')
            ->label($label)
            ->counts($relationship)
            ->badge()
            ->color($color);
            
        if ($alignCenter) {
            $column->alignCenter();
        }
        
        return $column;
    }

    /**
     * Standard date column
     */
    public static function date(
        string $field,
        string $label,
        string $format = 'd/m/Y',
        bool $sortable = true,
        bool $since = false
    ): TextColumn {
        $column = TextColumn::make($field)
            ->label($label)
            ->date($format);
            
        if ($sortable) {
            $column->sortable();
        }
        
        if ($since) {
            $column->since();
        }
        
        return $column;
    }

    /**
     * Standard datetime column
     */
    public static function dateTime(
        string $field,
        string $label,
        string $format = 'd/m/Y H:i',
        bool $sortable = true,
        bool $since = false,
        bool $toggleable = false,
        bool $hiddenByDefault = false
    ): TextColumn {
        $column = TextColumn::make($field)
            ->label($label)
            ->dateTime($format);
            
        if ($sortable) {
            $column->sortable();
        }
        
        if ($since) {
            $column->since();
        }
        
        if ($toggleable) {
            $column->toggleable(isToggledHiddenByDefault: $hiddenByDefault);
        }
        
        return $column;
    }

    /**
     * Standard created_at column
     */
    public static function createdAt(
        string $label = 'Créé le',
        bool $hiddenByDefault = true
    ): TextColumn {
        return static::dateTime(
            'created_at',
            $label,
            toggleable: true,
            hiddenByDefault: $hiddenByDefault,
            since: true
        );
    }

    /**
     * Standard updated_at column
     */
    public static function updatedAt(
        string $label = 'Modifié le',
        bool $hiddenByDefault = true
    ): TextColumn {
        return static::dateTime(
            'updated_at',
            $label,
            toggleable: true,
            hiddenByDefault: $hiddenByDefault,
            since: true
        );
    }

    /**
     * Badge with relationship information and count
     */
    public static function relationshipBadges(
        string $relationshipName,
        string $label,
        string $displayField = 'name',
        int $maxDisplay = 2,
        string $color = 'info',
        callable $customState = null
    ): TextColumn {
        return TextColumn::make($relationshipName . '_display')
            ->label($label)
            ->badge()
            ->color($color)
            ->state(function ($record) use ($relationshipName, $displayField, $maxDisplay, $customState) {
                if ($customState) {
                    return $customState($record);
                }
                
                $items = $record->{$relationshipName}->pluck($displayField)->toArray();
                if (empty($items)) {
                    return null;
                }
                
                $displayItems = array_slice($items, 0, $maxDisplay);
                $remaining = count($items) - count($displayItems);
                
                $result = implode(', ', $displayItems);
                if ($remaining > 0) {
                    $result .= " (+{$remaining})";
                }
                return $result;
            })
            ->searchable()
            ->wrap()
            ->tooltip(function ($record) use ($relationshipName, $displayField) {
                $items = $record->{$relationshipName}->pluck($displayField)->toArray();
                if (empty($items)) {
                    return 'Aucun élément assigné';
                }
                $count = count($items);
                $tooltip = implode(', ', $items);
                if ($count > 1) {
                    $tooltip .= " (total: {$count} éléments)";
                }
                return $tooltip;
            })
            ->placeholder('Aucun élément');
    }

    /**
     * Festivals display column
     */
    public static function festivalsDisplay(string $label = 'Festivals assignés'): TextColumn
    {
        return static::relationshipBadges('festivals', $label, 'name', 2, 'info');
    }

    /**
     * Standard copyable subdomain column
     */
    public static function subdomain(string $label = 'Sous-domaine'): TextColumn
    {
        return TextColumn::make('subdomain')
            ->label($label)
            ->searchable()
            ->sortable()
            ->copyable()
            ->copyMessage('Sous-domaine copié')
            ->badge()
            ->color('gray');
    }

    /**
     * Website URL column
     */
    public static function website(string $label = 'Site web'): TextColumn
    {
        return TextColumn::make('website')
            ->label($label)
            ->url(fn ($record) => $record->website)
            ->openUrlInNewTab()
            ->limit(30)
            ->tooltip(fn ($record) => $record->website);
    }

    /**
     * Duration column with minutes suffix
     */
    public static function duration(string $label = 'Durée'): TextColumn
    {
        return TextColumn::make('duration')
            ->label($label)
            ->suffix(' min')
            ->numeric()
            ->sortable()
            ->placeholder('N/A');
    }

    /**
     * Year column
     */
    public static function year(string $label = 'Année'): TextColumn
    {
        return TextColumn::make('year')
            ->label($label)
            ->numeric()
            ->sortable()
            ->alignCenter();
    }
}
