<?php

namespace Modules\Fresnel\app\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FestivalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'edition' => $this->edition,
            'year' => $this->year,
            'city' => $this->city,
            'country' => $this->country,
            'start_date' => $this->start_date?->toISOString(),
            'end_date' => $this->end_date?->toISOString(),
            'description' => $this->description,
            'website' => $this->website,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'logo_url' => $this->logo_url,
            'banner_url' => $this->banner_url,

            // Status and configuration
            'status' => $this->status,
            'is_active' => $this->is_active,
            'is_public' => $this->is_public,
            'allows_submissions' => $this->allows_submissions,
            'submission_deadline' => $this->submission_deadline?->toISOString(),
            'notification_date' => $this->notification_date?->toISOString(),

            // DCP Requirements
            'dcp_requirements' => $this->when(
                $request->includeRequirements ?? false,
                $this->dcp_requirements ? json_decode($this->dcp_requirements, true) : null
            ),

            // Technical specifications
            'technical_specs' => $this->when(
                $request->includeSpecs ?? false,
                [
                    'supported_formats' => $this->supported_formats ? json_decode($this->supported_formats, true) : [],
                    'max_file_size' => $this->max_file_size,
                    'required_subtitles' => $this->required_subtitles ? json_decode($this->required_subtitles, true) : [],
                    'audio_requirements' => $this->audio_requirements ? json_decode($this->audio_requirements, true) : null,
                ]
            ),

            // Statistics
            'movies_count' => $this->whenLoaded('movies', fn () => $this->movies->count(), 0),
            'completed_submissions' => $this->whenLoaded('movies', fn () => $this->movies->where('dcp_status', 'completed')->count(), 0),
            'pending_submissions' => $this->whenLoaded('movies', fn () => $this->movies->where('dcp_status', 'processing')->count(), 0),
            'failed_submissions' => $this->whenLoaded('movies', fn () => $this->movies->where('dcp_status', 'failed')->count(), 0),

            // Relationships
            'movies' => $this->when(
                $request->includeMovies ?? false,
                MovieResource::collection($this->whenLoaded('movies'))
            ),
            'organizer' => $this->when($this->organizer, new UserResource($this->organizer)),

            // Computed attributes
            'duration_days' => $this->start_date && $this->end_date
                ? $this->start_date->diffInDays($this->end_date) + 1
                : null,
            'is_upcoming' => $this->start_date ? $this->start_date->isFuture() : false,
            'is_ongoing' => $this->start_date && $this->end_date
                ? $this->start_date->isPast() && $this->end_date->isFuture()
                : false,
            'is_past' => $this->end_date ? $this->end_date->isPast() : false,
            'submission_open' => $this->allows_submissions &&
                ($this->submission_deadline ? $this->submission_deadline->isFuture() : true),
            'days_until_start' => $this->start_date && $this->start_date->isFuture()
                ? now()->diffInDays($this->start_date)
                : null,
            'days_until_deadline' => $this->submission_deadline && $this->submission_deadline->isFuture()
                ? now()->diffInDays($this->submission_deadline)
                : null,

            // Permissions
            'can_submit' => $request->user() ? $this->canUserSubmit($request->user()) : false,
            'can_edit' => $request->user()?->can('update', $this->resource) ?? false,
            'can_delete' => $request->user()?->can('delete', $this->resource) ?? false,
            'can_manage_submissions' => $request->user()?->can('manage-submissions', $this->resource) ?? false,

            // Timestamps
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // API metadata
            'links' => [
                'self' => route('api.v1.festivals.show', $this->id),
                'movies' => route('api.v1.festivals.movies', $this->id),
                'statistics' => route('api.v1.festivals.statistics', $this->id),
                'bulk_upload' => $this->when(
                    $request->user()?->can('manage-submissions', $this->resource),
                    route('api.v1.festivals.bulk-upload', $this->id)
                ),
            ],
        ];
    }

    /**
     * Check if user can submit to this festival
     */
    private function canUserSubmit($user): bool
    {
        return $this->allows_submissions &&
               $this->is_active &&
               ($this->submission_deadline ? $this->submission_deadline->isFuture() : true) &&
               $user->can('submit-to-festival', $this->resource);
    }
}
