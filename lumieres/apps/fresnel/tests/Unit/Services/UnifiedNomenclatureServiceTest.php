<?php

namespace Tests\Unit\Services;

use App\Services\UnifiedNomenclatureService;
use App\Models\Movie;
use App\Models\Festival;
use App\Models\Parameter;
use App\Models\Nomenclature;
use App\Models\MovieParameter;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class UnifiedNomenclatureServiceTest extends TestCase
{
    use RefreshDatabase;

    private UnifiedNomenclatureService $nomenclatureService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->nomenclatureService = new UnifiedNomenclatureService();
    }

    /** @test */
    public function it_can_generate_movie_nomenclature_with_parameters()
    {
        // Arrange
        $festival = Festival::factory()->create();
        $movie = Movie::factory()->create(['title' => 'The Matrix']);

        // Create parameters
        $titleParam = Parameter::factory()->create(['name' => 'title', 'code' => 'TITLE']);
        $yearParam = Parameter::factory()->create(['name' => 'year', 'code' => 'YEAR']);
        $formatParam = Parameter::factory()->create(['name' => 'format', 'code' => 'FORMAT']);

        // Create nomenclatures for the festival
        Nomenclature::factory()->create([
            'festival_id' => $festival->id,
            'parameter_id' => $titleParam->id,
            'order_position' => 1,
            'is_active' => true,
            'is_required' => true
        ]);

        Nomenclature::factory()->create([
            'festival_id' => $festival->id,
            'parameter_id' => $yearParam->id,
            'order_position' => 2,
            'is_active' => true,
            'is_required' => false
        ]);

        // Create movie parameters
        MovieParameter::factory()->create([
            'movie_id' => $movie->id,
            'parameter_id' => $titleParam->id,
            'value' => 'The Matrix'
        ]);

        MovieParameter::factory()->create([
            'movie_id' => $movie->id,
            'parameter_id' => $yearParam->id,
            'value' => '1999'
        ]);

        // Act
        $nomenclature = $this->nomenclatureService->generateMovieNomenclature($movie, $festival);

        // Assert
        $this->assertStringContainsString('The Matrix', $nomenclature);
        $this->assertStringContainsString('1999', $nomenclature);
        $this->assertStringContainsString('_', $nomenclature); // Default separator
    }

    /** @test */
    public function it_returns_default_nomenclature_when_no_active_nomenclatures()
    {
        // Arrange
        $festival = Festival::factory()->create();
        $movie = Movie::factory()->create(['title' => 'Test Movie', 'id' => 123]);

        // Act
        $nomenclature = $this->nomenclatureService->generateMovieNomenclature($movie, $festival);

        // Assert
        $this->assertStringContainsString('test_movie', strtolower($nomenclature));
        $this->assertStringContainsString('123', $nomenclature);
        $this->assertMatchesRegularExpression('/\d{8}/', $nomenclature); // Date format
    }

    /** @test */
    public function it_can_configure_festival_nomenclature()
    {
        // Arrange
        $festival = Festival::factory()->create();

        $parameterConfigs = [
            [
                'parameter_name' => 'title',
                'order_position' => 1,
                'is_required' => true,
                'prefix' => '',
                'suffix' => ''
            ],
            [
                'parameter_name' => 'year',
                'order_position' => 2,
                'is_required' => false,
                'prefix' => '(',
                'suffix' => ')'
            ],
            [
                'parameter_name' => 'format',
                'order_position' => 3,
                'is_required' => true,
                'default_value' => 'DCP'
            ]
        ];

        // Act
        $result = $this->nomenclatureService->configureFestivalNomenclature($festival, $parameterConfigs);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertCount(3, $result['nomenclatures']);

        // Verify nomenclatures were created
        $nomenclatures = Nomenclature::where('festival_id', $festival->id)
                                   ->where('is_active', true)
                                   ->orderBy('order_position')
                                   ->get();

        $this->assertCount(3, $nomenclatures);
        $this->assertEquals('title', $nomenclatures[0]->parameter->name);
        $this->assertTrue($nomenclatures[0]->is_required);
        $this->assertEquals('(', $nomenclatures[1]->prefix);
        $this->assertEquals(')', $nomenclatures[1]->suffix);
    }

    /** @test */
    public function it_can_preview_nomenclature()
    {
        // Arrange
        $festival = Festival::factory()->create();
        $movie = Movie::factory()->create(['title' => 'Inception']);

        $titleParam = Parameter::factory()->create(['name' => 'title']);
        $yearParam = Parameter::factory()->create(['name' => 'year']);

        Nomenclature::factory()->create([
            'festival_id' => $festival->id,
            'parameter_id' => $titleParam->id,
            'order_position' => 1,
            'is_required' => true
        ]);

        Nomenclature::factory()->create([
            'festival_id' => $festival->id,
            'parameter_id' => $yearParam->id,
            'order_position' => 2,
            'is_required' => false
        ]);

        $parameterValues = [
            'title' => 'Inception',
            'year' => '2010'
        ];

        // Act
        $preview = $this->nomenclatureService->previewNomenclature($movie, $festival, $parameterValues);

        // Assert
        $this->assertTrue($preview['is_valid']);
        $this->assertEmpty($preview['warnings']);
        $this->assertCount(2, $preview['preview_parts']);
        $this->assertStringContainsString('Inception', $preview['final_nomenclature']);
        $this->assertStringContainsString('2010', $preview['final_nomenclature']);
    }

    /** @test */
    public function it_detects_missing_required_parameters_in_preview()
    {
        // Arrange
        $festival = Festival::factory()->create();
        $movie = Movie::factory()->create(['title' => 'Test Movie']);

        $titleParam = Parameter::factory()->create(['name' => 'title']);
        $yearParam = Parameter::factory()->create(['name' => 'year']);

        Nomenclature::factory()->create([
            'festival_id' => $festival->id,
            'parameter_id' => $titleParam->id,
            'order_position' => 1,
            'is_required' => true
        ]);

        Nomenclature::factory()->create([
            'festival_id' => $festival->id,
            'parameter_id' => $yearParam->id,
            'order_position' => 2,
            'is_required' => true
        ]);

        $parameterValues = [
            'title' => 'Test Movie',
            // Missing required 'year' parameter
        ];

        // Act
        $preview = $this->nomenclatureService->previewNomenclature($movie, $festival, $parameterValues);

        // Assert
        $this->assertFalse($preview['is_valid']);
        $this->assertNotEmpty($preview['warnings']);
        $this->assertStringContainsString('year', $preview['warnings'][0]);
    }

    /** @test */
    public function it_can_extract_parameters_from_dcp_metadata()
    {
        // Arrange
        $dcpMetadata = [
            'ContentTitleText' => 'Avatar: The Way of Water',
            'MainPicture' => [
                'Duration' => 192,
                'FrameRate' => '24',
                'ScreenAspectRatio' => '2.39:1'
            ],
            'MainSound' => [
                'ChannelCount' => 6
            ],
            'IssueDate' => '2022-12-16T10:00:00Z'
        ];

        $movie = Movie::factory()->create([
            'DCP_metadata' => json_encode($dcpMetadata)
        ]);

        // Act
        $result = $this->nomenclatureService->extractParametersFromDcp($movie);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['extracted_params']);
        $this->assertEquals('Avatar: The Way of Water', $result['extracted_params']['title'] ?? null);
        $this->assertEquals(192, $result['extracted_params']['duration'] ?? null);
        $this->assertEquals('24', $result['extracted_params']['frame_rate'] ?? null);
    }

    /** @test */
    public function it_handles_invalid_dcp_metadata_gracefully()
    {
        // Arrange
        $movie = Movie::factory()->create([
            'DCP_metadata' => 'invalid json'
        ]);

        // Act
        $result = $this->nomenclatureService->extractParametersFromDcp($movie);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('invalides', $result['message']);
        $this->assertEmpty($result['extracted_params']);
    }

    /** @test */
    public function it_can_validate_nomenclature_format()
    {
        // Arrange
        $festival = Festival::factory()->create();
        
        // Create some nomenclature rules
        $titleParam = Parameter::factory()->create(['name' => 'title']);
        $yearParam = Parameter::factory()->create(['name' => 'year']);

        Nomenclature::factory()->create([
            'festival_id' => $festival->id,
            'parameter_id' => $titleParam->id,
            'is_required' => true,
            'is_active' => true
        ]);

        Nomenclature::factory()->create([
            'festival_id' => $festival->id,
            'parameter_id' => $yearParam->id,
            'is_required' => true,
            'is_active' => true
        ]);

        // Act - Valid nomenclature
        $validResult = $this->nomenclatureService->validateNomenclature('Avatar_2022', $festival);

        // Assert
        $this->assertTrue($validResult['is_valid']);
        $this->assertEmpty($validResult['issues']);
        $this->assertGreaterThan(50, $validResult['score']);

        // Act - Invalid nomenclature (too long)
        $longNomenclature = str_repeat('Very_Long_Movie_Title_', 20);
        $invalidResult = $this->nomenclatureService->validateNomenclature($longNomenclature, $festival);

        // Assert
        $this->assertFalse($invalidResult['is_valid']);
        $this->assertNotEmpty($invalidResult['issues']);
        $this->assertStringContainsString('trop longue', $invalidResult['issues'][0]);
    }

    /** @test */
    public function it_can_generate_nomenclature_statistics()
    {
        // Arrange
        $festival = Festival::factory()->create();
        
        // Create parameters and nomenclature
        $titleParam = Parameter::factory()->create(['name' => 'title']);
        $yearParam = Parameter::factory()->create(['name' => 'year']);

        Nomenclature::factory()->create([
            'festival_id' => $festival->id,
            'parameter_id' => $titleParam->id,
            'is_active' => true
        ]);

        // Create movies with parameters
        $movies = Movie::factory()->count(5)->create();
        
        foreach ($movies as $index => $movie) {
            $movie->festivals()->attach($festival);
            
            // Some movies have complete parameters, some don't
            if ($index < 3) {
                MovieParameter::factory()->create([
                    'movie_id' => $movie->id,
                    'parameter_id' => $titleParam->id,
                    'value' => "Movie Title $index"
                ]);
            }
        }

        // Act
        $stats = $this->nomenclatureService->getNomenclatureStats($festival);

        // Assert
        $this->assertEquals(5, $stats['total_movies']);
        $this->assertIsArray($stats['most_used_parameters']);
        $this->assertIsArray($stats['parameter_completion_rate']);
        $this->assertIsArray($stats['nomenclature_patterns']);
    }

    /** @test */
    public function it_caches_active_nomenclatures()
    {
        // Arrange
        $festival = Festival::factory()->create();
        $titleParam = Parameter::factory()->create(['name' => 'title']);

        $nomenclature = Nomenclature::factory()->create([
            'festival_id' => $festival->id,
            'parameter_id' => $titleParam->id,
            'is_active' => true
        ]);

        // Act - First call should cache the result
        $movie = Movie::factory()->create();
        $result1 = $this->nomenclatureService->generateMovieNomenclature($movie, $festival);

        // Verify cache was set
        $cacheKey = "nomenclature_active_{$festival->id}";
        $this->assertTrue(Cache::has($cacheKey));

        // Act - Second call should use cache
        $result2 = $this->nomenclatureService->generateMovieNomenclature($movie, $festival);

        // Assert
        $this->assertEquals($result1, $result2);
    }

    /** @test */
    public function it_handles_nomenclature_formatting_rules()
    {
        // Arrange
        $festival = Festival::factory()->create();
        $movie = Movie::factory()->create();

        $titleParam = Parameter::factory()->create(['name' => 'title']);

        $nomenclature = Nomenclature::factory()->create([
            'festival_id' => $festival->id,
            'parameter_id' => $titleParam->id,
            'order_position' => 1,
            'prefix' => '[',
            'suffix' => ']',
            'is_active' => true
        ]);

        MovieParameter::factory()->create([
            'movie_id' => $movie->id,
            'parameter_id' => $titleParam->id,
            'value' => 'Test Movie'
        ]);

        // Act
        $result = $this->nomenclatureService->generateMovieNomenclature($movie, $festival);

        // Assert
        $this->assertStringContainsString('[Test Movie]', $result);
    }
}
