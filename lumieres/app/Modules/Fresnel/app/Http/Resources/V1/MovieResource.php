<?php

namespace Modules\Fresnel\app\Http\Resources\V1;

use Illuminate\Http\Request;
use Modules\Fresnel\app\Http\Resources\BaseApiResource;

class MovieResource extends BaseApiResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'original_title' => $this->original_title,
            'director' => $this->director,
            'year' => $this->year,
            'duration' => $this->duration,
            'genre' => $this->genre,
            'rating' => $this->rating,
            'synopsis' => $this->synopsis,
            'poster_url' => $this->poster_url,
            'trailer_url' => $this->trailer_url,

            // DCP specific information
            'dcp_status' => $this->dcp_status,
            'dcp_path' => $this->whenCan($request, 'view-sensitive-data', $this->dcp_path),
            'dcp_size' => $this->dcp_size,
            'dcp_checksum' => $this->dcp_checksum,
            'dcp_created_at' => $this->formatDate($this->dcp_created_at),
            'dcp_validated_at' => $this->formatDate($this->dcp_validated_at),

            // Technical metadata
            'technical_metadata' => $this->whenRequested(
                $request,
                'includeMetadata',
                $this->jsonToArray($this->technical_metadata)
            ),

            // Processing information
            'processing_status' => $this->processing_status,
            'processing_progress' => $this->processing_progress,
            'last_processed_at' => $this->formatDate($this->last_processed_at),
            'processing_errors' => $this->whenRequested(
                $request,
                'includeErrors',
                $this->jsonToArray($this->processing_errors)
            ),

            // Upload information
            'upload_status' => $this->upload_status,
            'upload_progress' => $this->upload_progress,
            'uploaded_at' => $this->formatDate($this->uploaded_at),
            'uploaded_by' => $this->when($this->uploadedBy, new UserResource($this->uploadedBy)),

            // Relationships
            'festivals' => FestivalResource::collection($this->whenLoaded('festivals')),

            // Computed attributes
            'file_size_human' => $this->formatFileSize($this->dcp_size),
            'duration_human' => $this->formatDuration($this->duration),
            'validation_status' => $this->getValidationStatus(),

            // User permissions
            'can_download' => $this->userCan($request, 'download'),
            'can_edit' => $this->userCan($request, 'update'),
            'can_delete' => $this->userCan($request, 'delete'),

            // Timestamps
            'created_at' => $this->formatDate($this->created_at),
            'updated_at' => $this->formatDate($this->updated_at),

            // API metadata
            'links' => $this->generateMovieLinks($request),
        ];

        // Apply field filtering if requested
        return $this->filterFields($data, $request);
    }

    /**
     * Generate movie-specific links
     */
    private function generateMovieLinks(Request $request): array
    {
        return $this->generateLinks([
            'self' => route('api.v1.movies.show', $this->id),
            'dcp_status' => route('api.v1.movies.dcp-status', $this->id),
            'metadata' => route('api.v1.movies.metadata', $this->id),
            'processing_history' => route('api.v1.movies.processing-history', $this->id),
            'download' => $this->when(
                $this->dcp_status === 'completed' && $this->userCan($request, 'download'),
                route('api.v1.movies.download-dcp', $this->id)
            ),
        ]);
    }

    /**
     * Get validation status based on various checks
     */
    private function getValidationStatus(): array
    {
        return [
            'is_valid' => $this->dcp_validated_at !== null && $this->processing_errors === null,
            'has_dcp' => $this->dcp_status === 'completed',
            'has_metadata' => $this->technical_metadata !== null,
            'checksum_verified' => $this->dcp_checksum !== null,
            'last_validation' => $this->dcp_validated_at?->toISOString(),
        ];
    }
}
