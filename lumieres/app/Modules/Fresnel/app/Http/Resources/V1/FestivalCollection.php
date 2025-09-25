<?php

namespace Modules\Fresnel\app\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FestivalCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = FestivalResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->resource->total(),
                'count' => $this->resource->count(),
                'per_page' => $this->resource->perPage(),
                'current_page' => $this->resource->currentPage(),
                'last_page' => $this->resource->lastPage(),
                'from' => $this->resource->firstItem(),
                'to' => $this->resource->lastItem(),
                'has_more_pages' => $this->resource->hasMorePages(),

                // Festival statistics
                'statistics' => [
                    'active_festivals' => $this->collection->where('is_active', true)->count(),
                    'upcoming_festivals' => $this->collection->filter(fn ($f) => $f->start_date && $f->start_date->isFuture())->count(),
                    'ongoing_festivals' => $this->collection->filter(fn ($f) => $f->start_date && $f->end_date && $f->start_date->isPast() && $f->end_date->isFuture())->count(),
                    'accepting_submissions' => $this->collection->where('allows_submissions', true)->count(),
                    'countries' => $this->collection->pluck('country')->filter()->unique()->count(),
                    'total_submissions' => $this->collection->sum(fn ($f) => $f->movies_count ?? 0),
                ],
            ],
            'links' => [
                'first' => $this->resource->url(1),
                'last' => $this->resource->url($this->resource->lastPage()),
                'prev' => $this->resource->previousPageUrl(),
                'next' => $this->resource->nextPageUrl(),
            ],
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'filters_applied' => [
                'status' => $request->get('status'),
                'country' => $request->get('country'),
                'year' => $request->get('year'),
                'accepts_submissions' => $request->get('accepts_submissions'),
                'search' => $request->get('search'),
            ],
            'available_filters' => [
                'statuses' => ['draft', 'open', 'closed', 'completed'],
                'countries' => $this->getAvailableCountries(),
                'years' => $this->getAvailableYears(),
            ],
            'sort_options' => [
                'name' => 'Name A-Z',
                '-name' => 'Name Z-A',
                'start_date' => 'Start Date (Earliest first)',
                '-start_date' => 'Start Date (Latest first)',
                'year' => 'Year (Oldest first)',
                '-year' => 'Year (Newest first)',
                'created_at' => 'Created (Oldest first)',
                '-created_at' => 'Created (Newest first)',
            ],
        ];
    }

    /**
     * Get available countries from the collection
     */
    private function getAvailableCountries(): array
    {
        return $this->collection->pluck('country')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Get available years from the collection
     */
    private function getAvailableYears(): array
    {
        return $this->collection->pluck('year')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }
}
