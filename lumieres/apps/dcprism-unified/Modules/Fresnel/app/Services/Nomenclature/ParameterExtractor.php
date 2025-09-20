<?php

namespace Modules\Fresnel\app\Services\Nomenclature;

use Illuminate\Support\Facades\Log;
use Modules\Fresnel\app\Models\Movie;
use Modules\Fresnel\app\Models\MovieParameter;
use Modules\Fresnel\app\Models\Parameter;

/**
 * Service for extracting parameter values from movies and DCP metadata
 */
class ParameterExtractor
{
    /**
     * Get parameter value for a movie with rich fallback logic
     */
    public function getParameterValueForMovie(Movie $movie, Parameter $parameter): mixed
    {
        // First, try to get from movie parameters
        $movieParameter = MovieParameter::where('movie_id', $movie->id)
            ->where('parameter_id', $parameter->id)
            ->first();

        if ($movieParameter) {
            return $movieParameter->value;
        }

        // Enhanced fallback: check direct movie properties for standard parameters
        $directValue = $this->getDirectMovieProperty($movie, $parameter->name);
        if ($directValue !== null) {
            return $directValue;
        }

        // Final fallback to parameter default value
        return $parameter->default_value;
    }

    /**
     * Get direct movie property values for standard parameters
     */
    private function getDirectMovieProperty(Movie $movie, string $parameterName): mixed
    {
        return match ($parameterName) {
            'title' => $movie->title,
            'year' => $movie->year,
            'duration' => $movie->duration,
            'format' => $movie->format,
            'genre' => $movie->genre,
            'country' => $movie->country,
            'language' => $movie->language,
            default => null
        };
    }

    /**
     * Extract parameters from DCP metadata
     */
    public function extractFromDcpMetadata(Movie $movie): array
    {
        if (empty($movie->DCP_metadata)) {
            return [
                'success' => false,
                'message' => 'No DCP metadata available',
                'extracted_params' => [],
            ];
        }

        $metadata = is_string($movie->DCP_metadata)
            ? json_decode($movie->DCP_metadata, true)
            : $movie->DCP_metadata;

        if (! $metadata) {
            return [
                'success' => false,
                'message' => 'Invalid DCP metadata format',
                'extracted_params' => [],
            ];
        }

        $extractedParams = [];

        // Extract common parameters
        $extractedParams = array_merge($extractedParams, [
            'resolution' => $this->extractResolution($metadata),
            'frame_rate' => $this->extractFrameRate($metadata),
            'audio_channels' => $this->extractAudioChannels($metadata),
            'duration' => $this->extractDuration($metadata),
            'aspect_ratio' => $this->extractAspectRatio($metadata),
        ]);

        // Remove null values
        $extractedParams = array_filter($extractedParams, fn ($value) => $value !== null);

        Log::info('[ParameterExtractor] Extracted parameters from DCP metadata', [
            'movie_id' => $movie->id,
            'extracted_count' => count($extractedParams),
            'parameters' => array_keys($extractedParams),
        ]);

        return [
            'success' => true,
            'message' => 'Parameters extracted successfully',
            'extracted_params' => $extractedParams,
        ];
    }

    /**
     * Store extracted parameters as MovieParameters
     */
    public function storeExtractedParameters(Movie $movie, array $extractedParams): void
    {
        foreach ($extractedParams as $parameterName => $value) {
            $parameter = Parameter::where('name', $parameterName)
                ->orWhere('key', $parameterName)
                ->first();

            if (! $parameter) {
                Log::debug('[ParameterExtractor] Parameter not found, skipping', [
                    'parameter_name' => $parameterName,
                    'movie_id' => $movie->id,
                ]);

                continue;
            }

            MovieParameter::updateOrCreate(
                [
                    'movie_id' => $movie->id,
                    'parameter_id' => $parameter->id,
                ],
                [
                    'value' => $value,
                    'status' => MovieParameter::STATUS_VALIDATED,
                    'extraction_method' => MovieParameter::EXTRACTION_AUTOMATIC,
                    'extracted_at' => now(),
                ]
            );
        }

        Log::info('[ParameterExtractor] Stored extracted parameters', [
            'movie_id' => $movie->id,
            'stored_count' => count($extractedParams),
        ]);
    }

    /**
     * Extract resolution from metadata
     */
    private function extractResolution(array $metadata): ?string
    {
        if (isset($metadata['video']['width'], $metadata['video']['height'])) {
            return $metadata['video']['width'].'x'.$metadata['video']['height'];
        }

        if (isset($metadata['resolution'])) {
            return $metadata['resolution'];
        }

        return null;
    }

    /**
     * Extract frame rate from metadata
     */
    private function extractFrameRate(array $metadata): ?string
    {
        return $metadata['video']['frame_rate'] ??
               $metadata['frame_rate'] ??
               null;
    }

    /**
     * Extract audio channels from metadata
     */
    private function extractAudioChannels(array $metadata): ?int
    {
        return $metadata['audio']['channels'] ??
               $metadata['channels'] ??
               null;
    }

    /**
     * Extract duration from metadata
     */
    private function extractDuration(array $metadata): ?int
    {
        $duration = $metadata['duration'] ??
                   $metadata['video']['duration'] ??
                   null;

        return $duration ? (int) $duration : null;
    }

    /**
     * Extract aspect ratio from metadata
     */
    private function extractAspectRatio(array $metadata): ?string
    {
        return $metadata['video']['aspect_ratio'] ??
               $metadata['aspect_ratio'] ??
               null;
    }
}
