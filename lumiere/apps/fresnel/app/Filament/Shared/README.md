# Filament Shared Components Architecture

This directory contains reusable Filament components, widgets, and utilities that eliminate code duplication across different panels (Manager, Tech, Source, etc.).

## 📁 Directory Structure

```
app/Filament/Shared/
├── Forms/
│   └── Fields.php              # Reusable form field components
├── Tables/
│   └── Columns.php             # Reusable table column components
├── Widgets/
│   ├── BaseStatsWidget.php     # Base class for statistics widgets
│   ├── BaseFestivalAwareWidget.php # Festival-aware statistics widget
│   └── FestivalSelectorWidget.php  # Festival selector widget
├── Concerns/
│   ├── HasFestivalContext.php  # Festival context management trait
│   └── HasRoleBasedAccess.php  # Role-based access control trait
└── README.md                   # This documentation
```

## 🎯 Purpose

This shared architecture provides:

- **Code Consistency**: Standardized components across all panels
- **DRY Principle**: Eliminate duplicate form fields, table columns, and widgets
- **Maintainability**: Single source of truth for common components
- **Scalability**: Easy to extend and modify shared behavior
- **Role-based Logic**: Centralized access control and permission handling
- **Festival Context**: Unified festival selection and filtering

## 📝 Components Overview

### Forms/Fields.php

Provides standardized form field components:

```php
// Standard fields
Fields::title()           // Standard title field with validation
Fields::name()           // Standard name field
Fields::email()          // Email field with validation and unique options
Fields::description()    // Textarea with consistent styling
Fields::status()         // Status select with customizable options

// Specialized fields
Fields::roleSelect()     // Role selection dropdown
Fields::password()       // Password + confirmation fields
Fields::isActive()       // Active/inactive toggle
Fields::year()           // Year field with validation
Fields::duration()       // Duration with minutes suffix
Fields::website()        // URL field with validation
Fields::dcpFileUpload()  // DCP file upload component
```

**Usage Example:**
```php
use App\Filament\Shared\Forms\Fields;

$schema = [
    Fields::title('Titre du film'),
    Fields::email('Email de contact', unique: true),
    Fields::roleSelect(),
    ...Fields::password(),
];
```

### Tables/Columns.php

Provides standardized table column components:

```php
// Basic columns
Columns::name()                    // Name/title with search & sort
Columns::title()                   // Title column with limit
Columns::email()                   // Email with copy functionality
Columns::createdAt()              // Standardized created_at column
Columns::updatedAt()              // Standardized updated_at column

// Specialized columns
Columns::roleBadge()              // Role badge with colors
Columns::statusBadge()            // Customizable status badge
Columns::activeBadge()            // Active/inactive status
Columns::emailVerificationIcon()  // Email verification status
Columns::festivalsDisplay()       // Festival relationships display
Columns::countBadge()             // Count relationships
```

**Usage Example:**
```php
use App\Filament\Shared\Tables\Columns;

$columns = [
    Columns::name('name', 'Nom'),
    Columns::email(),
    Columns::roleBadge(),
    Columns::activeBadge(),
    Columns::createdAt(),
];
```

### Widgets Base Classes

#### BaseStatsWidget
Base class for all statistics widgets with standardized styling and utilities.

```php
abstract class MyStatsWidget extends BaseStatsWidget
{
    protected function getStatsData(): array
    {
        return [
            $this->createStat('Label', 100, 'Description', 'icon', 'success'),
        ];
    }
}
```

#### BaseFestivalAwareWidget
Extends BaseStatsWidget with festival context awareness.

```php
abstract class MyFestivalWidget extends BaseFestivalAwareWidget
{
    protected function getFestivalSpecificStats(): array
    {
        $moviesQuery = $this->getFestivalAwareQuery(Movie::class);
        $count = $moviesQuery->count();
        
        return [
            $this->createFestivalStat('Films', $count, 'Dans ce festival'),
        ];
    }
}
```

### Concerns (Traits)

#### HasFestivalContext
Provides festival selection and filtering capabilities:

```php
use App\Filament\Shared\Concerns\HasFestivalContext;

class MyResource extends Resource
{
    use HasFestivalContext;
    
    protected function configureQuery($query)
    {
        return $this->applyFestivalFilter($query);
    }
    
    protected function getFestivalSelector()
    {
        return $this->getFestivalSelector(); // Pre-built festival selector
    }
}
```

#### HasRoleBasedAccess
Provides role-based access control methods:

```php
use App\Filament\Shared\Concerns\HasRoleBasedAccess;

class MyResource extends Resource
{
    use HasRoleBasedAccess;
    
    protected function configureActions($actions)
    {
        if ($this->hasWriteAccess()) {
            $actions->add(EditAction::make());
        }
        
        if ($this->canValidateContent()) {
            $actions->add(ValidateAction::make());
        }
    }
}
```

## 🔄 Migration Guide

To migrate existing components to use shared architecture:

### 1. Forms Migration

**Before:**
```php
TextInput::make('title')
    ->label('Titre')
    ->required()
    ->maxLength(255)
```

**After:**
```php
Fields::title()
```

### 2. Tables Migration

**Before:**
```php
TextColumn::make('name')
    ->label('Nom')
    ->searchable()
    ->sortable()
```

**After:**
```php
Columns::name('name', 'Nom')
```

### 3. Widgets Migration

**Before:**
```php
class MyWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total', 100)
                ->description('Items')
                ->color('success'),
        ];
    }
}
```

**After:**
```php
class MyWidget extends BaseStatsWidget
{
    protected function getStatsData(): array
    {
        return [
            $this->createStat('Total', 100, 'Items', null, 'success'),
        ];
    }
}
```

## 🎨 Customization

All shared components accept parameters for customization:

```php
// Customized fields
Fields::email('Custom Label', required: false, placeholder: 'Enter email...')

// Customized columns
Columns::statusBadge('status', 'Custom Status', [
    'success' => 'approved',
    'warning' => 'pending'
])

// Customized widgets with festival context
class CustomWidget extends BaseFestivalAwareWidget
{
    protected function getFestivalSpecificStats(): array
    {
        $context = $this->getFestivalContext();
        // Custom logic using festival context
    }
}
```

## 🧪 Testing

Components can be tested by verifying syntax:

```bash
php -l app/Filament/Shared/Forms/Fields.php
php -l app/Filament/Shared/Tables/Columns.php
php -l app/Filament/Shared/Concerns/HasFestivalContext.php
```

## 📋 Best Practices

1. **Use Shared Components First**: Always check if a shared component exists before creating custom ones
2. **Extend Don't Modify**: Extend base classes rather than modifying shared components directly
3. **Document Extensions**: When extending, document the specific use case
4. **Keep Generic**: Shared components should remain generic and configurable
5. **Test Changes**: Any modification to shared components should be tested across all panels

## 🔮 Future Enhancements

- [ ] Add more form components (file upload, date ranges, etc.)
- [ ] Create shared action components
- [ ] Add notification templates
- [ ] Create shared modal components
- [ ] Add theme customization utilities
- [ ] Implement caching for performance
- [ ] Add unit tests for shared components

---

This architecture significantly reduces code duplication and improves maintainability across the DCPrism Laravel application's Filament panels.
