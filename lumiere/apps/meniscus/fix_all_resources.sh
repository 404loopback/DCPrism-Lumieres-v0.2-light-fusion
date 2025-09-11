#!/bin/bash

echo "Fixing all Filament Resources..."

# Find all Resource.php files
find /home/inad/DCParty/frontend/app/Filament/Resources -name "*Resource.php" | while read file; do
    echo "Processing: $file"
    
    # Add necessary imports if missing
    if ! grep -q "use BackedEnum;" "$file"; then
        sed -i '/^use Filament\\Resources\\Resource;/a use BackedEnum;' "$file"
    fi
    
    if ! grep -q "use Filament\\\\Support\\\\Icons\\\\Heroicon;" "$file"; then
        sed -i '/^use BackedEnum;/a use Filament\\Support\\Icons\\Heroicon;' "$file"
    fi
    
    # Fix navigationIcon type
    sed -i 's/protected static ?string \$navigationIcon/protected static string|BackedEnum|null \$navigationIcon/g' "$file"
    
    # Comment out navigationGroup
    sed -i 's/protected static ?string \$navigationGroup = /\/\/ protected static ?string \$navigationGroup = /g' "$file"
    
    # Convert common icons to Heroicon enum
    sed -i "s/'heroicon-o-film'/Heroicon::OutlinedFilm/g" "$file"
    sed -i "s/'heroicon-o-cloud'/Heroicon::OutlinedCloud/g" "$file" 
    sed -i "s/'heroicon-o-server'/Heroicon::OutlinedServer/g" "$file"
    sed -i "s/'heroicon-o-users'/Heroicon::OutlinedUsers/g" "$file"
    sed -i "s/'heroicon-o-rectangle-stack'/Heroicon::OutlinedRectangleStack/g" "$file"
    sed -i "s/'heroicon-o-code-bracket'/Heroicon::OutlinedCodeBracket/g" "$file"
    sed -i "s/'heroicon-o-rocket-launch'/Heroicon::OutlinedRocketLaunch/g" "$file"
    sed -i "s/'heroicon-o-cog'/Heroicon::OutlinedCog/g" "$file"
    
done

echo "Done fixing all resources!"
