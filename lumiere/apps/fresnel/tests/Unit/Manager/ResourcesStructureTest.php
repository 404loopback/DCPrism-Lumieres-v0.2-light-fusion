<?php

namespace Tests\Unit\Manager;

use PHPUnit\Framework\TestCase;
use App\Filament\Manager\Resources\VersionResource;
use App\Filament\Manager\Resources\DcpResource;

class ResourcesStructureTest extends TestCase
{
    public function test_version_resource_has_correct_structure()
    {
        // Test que la classe existe
        $this->assertTrue(class_exists(VersionResource::class));
        
        // Test des propriétés statiques
        $this->assertEquals('App\Models\Version', VersionResource::$model);
        $this->assertEquals('heroicon-o-globe-alt', VersionResource::$navigationIcon);
        $this->assertEquals('Versions', VersionResource::$navigationLabel);
        $this->assertEquals('Gestion Festival', VersionResource::$navigationGroup);
    }

    public function test_dcp_resource_has_correct_structure()
    {
        // Test que la classe existe
        $this->assertTrue(class_exists(DcpResource::class));
        
        // Test des propriétés statiques
        $this->assertEquals('App\Models\Dcp', DcpResource::$model);
        $this->assertEquals('heroicon-o-film', DcpResource::$navigationIcon);
        $this->assertEquals('DCPs', DcpResource::$navigationLabel);
        $this->assertEquals('Gestion Festival', DcpResource::$navigationGroup);
    }

    public function test_version_resource_has_required_methods()
    {
        $reflection = new \ReflectionClass(VersionResource::class);
        
        $this->assertTrue($reflection->hasMethod('table'));
        $this->assertTrue($reflection->hasMethod('getEloquentQuery'));
        $this->assertTrue($reflection->hasMethod('getPages'));
    }

    public function test_dcp_resource_has_required_methods()
    {
        $reflection = new \ReflectionClass(DcpResource::class);
        
        $this->assertTrue($reflection->hasMethod('table'));
        $this->assertTrue($reflection->hasMethod('getEloquentQuery'));
        $this->assertTrue($reflection->hasMethod('getPages'));
    }

    public function test_resources_have_security_methods()
    {
        $versionReflection = new \ReflectionClass(VersionResource::class);
        $dcpReflection = new \ReflectionClass(DcpResource::class);
        
        // Test que les méthodes de sécurité existent
        $this->assertTrue($versionReflection->hasMethod('canEditVersion'));
        $this->assertTrue($dcpReflection->hasMethod('canEditDcp'));
        $this->assertTrue($dcpReflection->hasMethod('canValidateDcp'));
        $this->assertTrue($dcpReflection->hasMethod('canRequestRevision'));
    }
}
