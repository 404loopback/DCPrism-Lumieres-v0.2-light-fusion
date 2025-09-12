<?php

$directory = __DIR__ . '/app/Filament/Resources';

function fixFilamentResource($filePath) {
    if (!file_exists($filePath)) {
        return;
    }
    
    echo "Fixing: $filePath\n";
    
    $content = file_get_contents($filePath);
    
    // Fix navigationGroup - remove it completely for now
    $content = preg_replace(
        '/protected static \?string \$navigationGroup = .*?;/', 
        '// protected static ?string $navigationGroup = null; // Disabled for Laravel 12 compatibility', 
        $content
    );
    
    // Fix navigationIcon - convert to Heroicon enum
    $iconMap = [
        "'heroicon-o-rocket-launch'" => 'Heroicon::OutlinedRocketLaunch',
        "'heroicon-o-cloud'" => 'Heroicon::OutlinedCloud',
        "'heroicon-o-users'" => 'Heroicon::OutlinedUsers',
        "'heroicon-o-code-bracket'" => 'Heroicon::OutlinedCodeBracket',
        "'heroicon-o-rectangle-stack'" => 'Heroicon::OutlinedRectangleStack',
        "'heroicon-o-server'" => 'Heroicon::OutlinedServer',
        "'heroicon-o-cog'" => 'Heroicon::OutlinedCog',
    ];
    
    foreach ($iconMap as $oldIcon => $newIcon) {
        $content = str_replace($oldIcon, $newIcon, $content);
    }
    
    // Fix the property type
    $content = preg_replace(
        '/protected static \?string \$navigationIcon = /', 
        'protected static string|BackedEnum|null \$navigationIcon = ', 
        $content
    );
    
    // Add necessary imports
    $needsHeroicon = strpos($content, 'Heroicon::') !== false;
    $needsNavigationGroup = strpos($content, 'NavigationGroup::') !== false;
    $needsBackedEnum = strpos($content, 'BackedEnum') !== false;
    
    $imports = [];
    if ($needsNavigationGroup) $imports[] = 'use App\\Enums\\NavigationGroup;';
    if ($needsHeroicon) $imports[] = 'use Filament\\Support\\Icons\\Heroicon;';
    if ($needsBackedEnum) $imports[] = 'use BackedEnum;';
    
    if (!empty($imports)) {
        $importString = implode("\n", $imports);
        $content = preg_replace(
            '/(namespace App\\Filament\\Resources;)/', 
            "$1\n\n$importString", 
            $content
        );
    }
    
    file_put_contents($filePath, $content);
}

// Find all Resource files
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($directory)
);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php' && 
        (strpos($file->getFilename(), 'Resource.php') !== false) &&
        !strpos($file->getPathname(), '.bak')) {
        
        fixFilamentResource($file->getPathname());
    }
}

echo "Done!\n";
