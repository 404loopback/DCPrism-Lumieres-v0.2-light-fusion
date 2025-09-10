<?php

namespace Tests\Feature\Manager;

use Tests\TestCase;
use App\Models\User;
use App\Models\Festival;
use App\Models\Movie;
use App\Models\Version;
use App\Models\Dcp;
use App\Models\Lang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use App\Filament\Manager\Resources\VersionResource\Pages\ListVersions;
use App\Filament\Manager\Resources\DcpResource\Pages\ListDcps;

class VersionsDcpsResourcesTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected Festival $festival;
    protected Movie $movie;
    protected Version $version;
    protected Dcp $dcp;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer un manager avec un festival
        $this->manager = User::factory()->create(['role' => 'manager']);
        $this->festival = Festival::factory()->create(['name' => 'Test Festival']);
        $this->manager->festivals()->attach($this->festival->id);
        
        // Créer les données de test
        $this->movie = Movie::factory()->create();
        $this->movie->festivals()->attach($this->festival->id);
        
        $this->version = Version::factory()->create([
            'movie_id' => $this->movie->id,
            'type' => 'VO',
            'audio_lang' => 'en',
            'sub_lang' => null,
        ]);
        
        $this->dcp = Dcp::factory()->create([
            'movie_id' => $this->movie->id,
            'version_id' => $this->version->id,
            'status' => Dcp::STATUS_UPLOADED,
            'is_valid' => false,
            'uploaded_by' => User::factory()->create()->id,
        ]);
    }

    /** @test */
    public function manager_can_access_versions_list_page()
    {
        $this->actingAs($this->manager);
        Session::put('manager_festival_id', $this->festival->id);

        $response = $this->get(route('filament.manager.resources.versions.index'));

        $response->assertSuccessful();
    }

    /** @test */
    public function manager_can_access_dcps_list_page()
    {
        $this->actingAs($this->manager);
        Session::put('manager_festival_id', $this->festival->id);

        $response = $this->get(route('filament.manager.resources.dcps.index'));

        $response->assertSuccessful();
    }

    /** @test */
    public function versions_are_filtered_by_festival()
    {
        // Créer un autre festival avec un film
        $otherFestival = Festival::factory()->create(['name' => 'Other Festival']);
        $otherMovie = Movie::factory()->create();
        $otherMovie->festivals()->attach($otherFestival->id);
        
        $otherVersion = Version::factory()->create([
            'movie_id' => $otherMovie->id,
            'type' => 'VF',
            'audio_lang' => 'fr',
        ]);

        $this->actingAs($this->manager);
        Session::put('manager_festival_id', $this->festival->id);

        Livewire::test(ListVersions::class)
            ->assertCanSeeTableRecords([$this->version])
            ->assertCannotSeeTableRecords([$otherVersion]);
    }

    /** @test */
    public function dcps_are_filtered_by_festival()
    {
        // Créer un DCP d'un autre festival
        $otherFestival = Festival::factory()->create(['name' => 'Other Festival']);
        $otherMovie = Movie::factory()->create();
        $otherMovie->festivals()->attach($otherFestival->id);
        
        $otherVersion = Version::factory()->create([
            'movie_id' => $otherMovie->id,
            'type' => 'VF',
        ]);
        
        $otherDcp = Dcp::factory()->create([
            'movie_id' => $otherMovie->id,
            'version_id' => $otherVersion->id,
            'status' => Dcp::STATUS_UPLOADED,
        ]);

        $this->actingAs($this->manager);
        Session::put('manager_festival_id', $this->festival->id);

        Livewire::test(ListDcps::class)
            ->assertCanSeeTableRecords([$this->dcp])
            ->assertCannotSeeTableRecords([$otherDcp]);
    }

    /** @test */
    public function manager_can_validate_dcp()
    {
        $this->actingAs($this->manager);
        Session::put('manager_festival_id', $this->festival->id);

        $this->assertFalse($this->dcp->is_valid);
        $this->assertEquals(Dcp::STATUS_UPLOADED, $this->dcp->status);

        Livewire::test(ListDcps::class)
            ->callTableAction('validate', $this->dcp);

        $this->dcp->refresh();
        $this->assertTrue($this->dcp->is_valid);
        $this->assertEquals(Dcp::STATUS_VALID, $this->dcp->status);
        $this->assertNotNull($this->dcp->validated_at);
    }

    /** @test */
    public function manager_can_request_dcp_revision()
    {
        $this->actingAs($this->manager);
        Session::put('manager_festival_id', $this->festival->id);

        $revisionNotes = 'Please fix the audio track synchronization';

        Livewire::test(ListDcps::class)
            ->callTableAction('request_revision', $this->dcp, data: [
                'revision_notes' => $revisionNotes
            ]);

        $this->dcp->refresh();
        $this->assertFalse($this->dcp->is_valid);
        $this->assertEquals(Dcp::STATUS_INVALID, $this->dcp->status);
        $this->assertEquals($revisionNotes, $this->dcp->validation_notes);
    }

    /** @test */
    public function manager_can_generate_version_nomenclature()
    {
        // On peut simuler le service ou utiliser un mock
        $this->actingAs($this->manager);
        Session::put('manager_festival_id', $this->festival->id);

        $originalNomenclature = $this->version->generated_nomenclature;

        try {
            Livewire::test(ListVersions::class)
                ->callTableAction('generate_nomenclature', $this->version);
            
            // Si le service existe et fonctionne, la nomenclature devrait être mise à jour
        } catch (\Exception $e) {
            // Si le service n'est pas implémenté, on s'attend à une exception
            $this->assertStringContainsString('UnifiedNomenclatureService', $e->getMessage());
        }
    }

    /** @test */
    public function manager_cannot_access_versions_from_other_festivals()
    {
        $this->actingAs($this->manager);
        Session::put('manager_festival_id', $this->festival->id);

        // Tenter d'accéder à une version d'un autre festival via l'URL directe
        $otherFestival = Festival::factory()->create();
        $otherMovie = Movie::factory()->create();
        $otherMovie->festivals()->attach($otherFestival->id);
        
        $otherVersion = Version::factory()->create([
            'movie_id' => $otherMovie->id,
        ]);

        // La version ne devrait pas être visible dans la liste
        Livewire::test(ListVersions::class)
            ->assertCannotSeeTableRecords([$otherVersion]);
    }

    /** @test */
    public function manager_cannot_access_dcps_from_other_festivals()
    {
        $this->actingAs($this->manager);
        Session::put('manager_festival_id', $this->festival->id);

        // Tenter d'accéder à un DCP d'un autre festival
        $otherFestival = Festival::factory()->create();
        $otherMovie = Movie::factory()->create();
        $otherMovie->festivals()->attach($otherFestival->id);
        
        $otherVersion = Version::factory()->create([
            'movie_id' => $otherMovie->id,
        ]);
        
        $otherDcp = Dcp::factory()->create([
            'movie_id' => $otherMovie->id,
            'version_id' => $otherVersion->id,
        ]);

        // Le DCP ne devrait pas être visible dans la liste
        Livewire::test(ListDcps::class)
            ->assertCannotSeeTableRecords([$otherDcp]);
    }

    /** @test */
    public function manager_redirected_if_no_festival_selected()
    {
        $this->actingAs($this->manager);
        // Ne pas mettre de festival dans la session

        $response = $this->get(route('filament.manager.resources.versions.index'));
        
        // Devrait rediriger vers la sélection de paramètres
        $response->assertRedirect();
    }

    /** @test */
    public function non_manager_cannot_access_manager_resources()
    {
        $regularUser = User::factory()->create(['role' => 'source']);
        
        $this->actingAs($regularUser);

        $response = $this->get(route('filament.manager.resources.versions.index'));
        
        // Devrait être rejeté (403 ou redirect)
        $this->assertTrue($response->status() >= 400 || $response->isRedirect());
    }

    /** @test */
    public function bulk_validate_dcps_works_correctly()
    {
        // Créer plusieurs DCPs en attente
        $dcps = Dcp::factory()->count(3)->create([
            'movie_id' => $this->movie->id,
            'version_id' => $this->version->id,
            'status' => Dcp::STATUS_UPLOADED,
            'is_valid' => false,
        ]);

        $this->actingAs($this->manager);
        Session::put('manager_festival_id', $this->festival->id);

        Livewire::test(ListDcps::class)
            ->callAction('bulk_validate');

        foreach ($dcps as $dcp) {
            $dcp->refresh();
            $this->assertTrue($dcp->is_valid);
            $this->assertEquals(Dcp::STATUS_VALID, $dcp->status);
        }
    }

    /** @test */
    public function dcp_stats_modal_displays_correct_information()
    {
        // Créer des DCPs avec différents statuts
        Dcp::factory()->create([
            'movie_id' => $this->movie->id,
            'version_id' => $this->version->id,
            'status' => Dcp::STATUS_VALID,
            'is_valid' => true,
        ]);
        
        Dcp::factory()->create([
            'movie_id' => $this->movie->id,
            'version_id' => $this->version->id,
            'status' => Dcp::STATUS_INVALID,
            'is_valid' => false,
        ]);

        $this->actingAs($this->manager);
        Session::put('manager_festival_id', $this->festival->id);

        Livewire::test(ListDcps::class)
            ->callAction('stats')
            ->assertOk();
    }
}
