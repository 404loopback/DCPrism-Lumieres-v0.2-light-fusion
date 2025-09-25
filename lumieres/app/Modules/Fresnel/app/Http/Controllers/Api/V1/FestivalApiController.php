<?php

namespace Modules\Fresnel\app\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Fresnel\app\Http\Controllers\Controller;
use Modules\Fresnel\app\Http\Resources\V1\FestivalCollection;
use Modules\Fresnel\app\Http\Resources\V1\FestivalResource;
use Modules\Fresnel\app\Http\Resources\V1\MovieResource;
use Modules\Fresnel\app\Jobs\BatchProcessingJob;
use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Models\Movie;

class FestivalApiController extends Controller
{
    /**
     * Display a paginated listing of festivals
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'sort' => 'string|in:name,-name,start_date,-start_date,year,-year,created_at,-created_at',
            'status' => 'string|in:draft,open,closed,completed',
            'country' => 'string|max:100',
            'year' => 'integer|min:1900|max:2030',
            'accepts_submissions' => 'boolean',
            'search' => 'string|max:255',
            'include_requirements' => 'boolean',
            'include_specs' => 'boolean',
            'include_movies' => 'boolean',
        ]);

        $query = Festival::query();

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('country')) {
            $query->where('country', 'LIKE', '%'.$request->country.'%');
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->has('accepts_submissions')) {
            $query->where('allows_submissions', $request->boolean('accepts_submissions'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('city', 'LIKE', "%{$search}%")
                    ->orWhere('country', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Apply sorting
        $sort = $request->get('sort', '-created_at');
        if (str_starts_with($sort, '-')) {
            $query->orderBy(substr($sort, 1), 'desc');
        } else {
            $query->orderBy($sort, 'asc');
        }

        // Include relationships for optimization
        $query->with(['organizer']);

        if ($request->boolean('include_movies')) {
            $query->with(['movies' => function ($q) {
                $q->limit(10)->latest();
            }]);
        }

        $perPage = min($request->get('per_page', 15), 100);
        $festivals = $query->paginate($perPage);

        return (new FestivalCollection($festivals))->toResponse($request);
    }

    /**
     * Store a newly created festival
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Festival::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'edition' => 'nullable|string|max:100',
            'year' => 'required|integer|min:1900|max:2030',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'description' => 'nullable|string',
            'website' => 'nullable|url',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string|max:50',
            'logo_url' => 'nullable|url',
            'banner_url' => 'nullable|url',
            'allows_submissions' => 'boolean',
            'submission_deadline' => 'nullable|date|after:today',
            'notification_date' => 'nullable|date|after:submission_deadline',
            'dcp_requirements' => 'nullable|array',
            'technical_specs' => 'nullable|array',
        ]);

        // Encode JSON fields
        if (isset($validated['dcp_requirements'])) {
            $validated['dcp_requirements'] = json_encode($validated['dcp_requirements']);
        }

        if (isset($validated['technical_specs'])) {
            $validated['technical_specs'] = json_encode($validated['technical_specs']);
        }

        $festival = Festival::create(array_merge($validated, [
            'organizer_id' => $request->user()->id,
            'status' => 'draft',
            'is_active' => true,
            'is_public' => false,
        ]));

        return response()->json([
            'message' => 'Festival created successfully',
            'data' => new FestivalResource($festival->load('organizer')),
        ], 201);
    }

    /**
     * Display the specified festival
     */
    public function show(Request $request, Festival $festival): JsonResponse
    {
        $this->authorize('view', $festival);

        $festival->load([
            'organizer',
            'movies' => function ($q) {
                $q->with(['processingJobs' => function ($jobQuery) {
                    $jobQuery->latest()->limit(3);
                }])->latest();
            },
        ]);

        return response()->json([
            'data' => new FestivalResource($festival),
        ]);
    }

    /**
     * Update the specified festival
     */
    public function update(Request $request, Festival $festival): JsonResponse
    {
        $this->authorize('update', $festival);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'edition' => 'nullable|string|max:100',
            'year' => 'sometimes|integer|min:1900|max:2030',
            'city' => 'sometimes|string|max:255',
            'country' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'description' => 'nullable|string',
            'website' => 'nullable|url',
            'contact_email' => 'sometimes|email',
            'contact_phone' => 'nullable|string|max:50',
            'logo_url' => 'nullable|url',
            'banner_url' => 'nullable|url',
            'allows_submissions' => 'boolean',
            'submission_deadline' => 'nullable|date',
            'notification_date' => 'nullable|date|after:submission_deadline',
            'status' => 'sometimes|in:draft,open,closed,completed',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'dcp_requirements' => 'nullable|array',
            'technical_specs' => 'nullable|array',
        ]);

        // Encode JSON fields
        if (isset($validated['dcp_requirements'])) {
            $validated['dcp_requirements'] = json_encode($validated['dcp_requirements']);
        }

        if (isset($validated['technical_specs'])) {
            $validated['technical_specs'] = json_encode($validated['technical_specs']);
        }

        $festival->update($validated);

        return response()->json([
            'message' => 'Festival updated successfully',
            'data' => new FestivalResource($festival->load('organizer', 'movies')),
        ]);
    }

    /**
     * Remove the specified festival
     */
    public function destroy(Festival $festival): JsonResponse
    {
        $this->authorize('delete', $festival);

        // Check if festival has movies
        $movieCount = $festival->movies()->count();
        if ($movieCount > 0) {
            return response()->json([
                'message' => 'Cannot delete festival with associated movies',
                'errors' => ['festival' => ['Festival has '.$movieCount.' associated movies']],
            ], 422);
        }

        $festival->delete();

        return response()->json([
            'message' => 'Festival deleted successfully',
        ]);
    }

    /**
     * Get movies associated with the festival
     */
    public function movies(Request $request, Festival $festival): JsonResponse
    {
        $this->authorize('view', $festival);

        $request->validate([
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'status' => 'string|in:pending,uploading,processing,completed,failed',
        ]);

        $query = $festival->movies();

        if ($request->filled('status')) {
            $query->where('dcp_status', $request->status);
        }

        $query->with(['processingJobs' => function ($q) {
            $q->latest()->limit(3);
        }]);

        $perPage = min($request->get('per_page', 15), 100);
        $movies = $query->paginate($perPage);

        return response()->json([
            'data' => MovieResource::collection($movies),
            'meta' => [
                'current_page' => $movies->currentPage(),
                'last_page' => $movies->lastPage(),
                'per_page' => $movies->perPage(),
                'total' => $movies->total(),
                'festival' => [
                    'id' => $festival->id,
                    'name' => $festival->name,
                    'year' => $festival->year,
                ],
            ],
        ]);
    }

    /**
     * Attach a movie to the festival
     */
    public function attachMovie(Request $request, Festival $festival, Movie $movie): JsonResponse
    {
        $this->authorize('manage-submissions', $festival);

        // Check if already attached
        if ($festival->movies()->where('movie_id', $movie->id)->exists()) {
            return response()->json([
                'message' => 'Movie already associated with festival',
                'data' => ['already_attached' => true],
            ]);
        }

        // Attach with pivot data
        $festival->movies()->attach($movie->id, [
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        return response()->json([
            'message' => 'Movie successfully attached to festival',
            'data' => [
                'festival_id' => $festival->id,
                'movie_id' => $movie->id,
                'attached_at' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * Detach a movie from the festival
     */
    public function detachMovie(Request $request, Festival $festival, Movie $movie): JsonResponse
    {
        $this->authorize('manage-submissions', $festival);

        $detached = $festival->movies()->detach($movie->id);

        if (! $detached) {
            return response()->json([
                'message' => 'Movie not associated with festival',
                'errors' => ['movie' => ['Movie is not associated with this festival']],
            ], 404);
        }

        return response()->json([
            'message' => 'Movie successfully detached from festival',
        ]);
    }

    /**
     * Get festival statistics
     */
    public function statistics(Request $request, Festival $festival): JsonResponse
    {
        $this->authorize('view', $festival);

        $cacheKey = "festival_stats:{$festival->id}";

        $stats = Cache::remember($cacheKey, 300, function () use ($festival) {
            return [
                'total_movies' => $festival->movies()->count(),
                'submissions_by_status' => [
                    'pending' => $festival->movies()->where('dcp_status', 'pending')->count(),
                    'uploading' => $festival->movies()->where('dcp_status', 'uploading')->count(),
                    'processing' => $festival->movies()->where('dcp_status', 'processing')->count(),
                    'completed' => $festival->movies()->where('dcp_status', 'completed')->count(),
                    'failed' => $festival->movies()->where('dcp_status', 'failed')->count(),
                ],
                'total_dcp_size' => $festival->movies()->sum('dcp_size'),
                'genres_distribution' => $festival->movies()
                    ->select('genre', DB::raw('count(*) as count'))
                    ->groupBy('genre')
                    ->pluck('count', 'genre'),
                'submission_timeline' => $festival->movies()
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->where('created_at', '>=', now()->subDays(30))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->pluck('count', 'date'),
                'processing_performance' => [
                    'avg_processing_time' => $festival->movies()
                        ->whereNotNull('dcp_validated_at')
                        ->whereNotNull('uploaded_at')
                        ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, uploaded_at, dcp_validated_at)')),
                    'success_rate' => $festival->movies()->count() > 0
                        ? ($festival->movies()->where('dcp_status', 'completed')->count() / $festival->movies()->count()) * 100
                        : 0,
                ],
            ];
        });

        return response()->json([
            'data' => array_merge($stats, [
                'festival' => [
                    'id' => $festival->id,
                    'name' => $festival->name,
                    'year' => $festival->year,
                    'status' => $festival->status,
                ],
                'generated_at' => now()->toISOString(),
            ]),
        ]);
    }

    /**
     * Bulk upload movies to festival
     */
    public function bulkUpload(Request $request, Festival $festival): JsonResponse
    {
        $this->authorize('manage-submissions', $festival);

        $request->validate([
            'movie_ids' => 'required|array|min:1|max:50',
            'movie_ids.*' => 'integer|exists:movies,id',
            'operation' => 'required|in:attach,process,validate',
        ]);

        $movieIds = $request->movie_ids;
        $operation = $request->operation;

        try {
            DB::beginTransaction();

            switch ($operation) {
                case 'attach':
                    // Attach movies to festival
                    $attachData = [];
                    foreach ($movieIds as $movieId) {
                        if (! $festival->movies()->where('movie_id', $movieId)->exists()) {
                            $attachData[$movieId] = [
                                'submitted_at' => now(),
                                'status' => 'submitted',
                            ];
                        }
                    }

                    if (! empty($attachData)) {
                        $festival->movies()->attach($attachData);
                    }

                    $message = 'Movies successfully attached to festival';
                    break;

                case 'process':
                case 'validate':
                    // Queue batch processing job
                    $movies = Movie::whereIn('id', $movieIds)->get();
                    BatchProcessingJob::dispatch($movies, $operation)->onQueue('dcp-batch');

                    $message = 'Batch '.$operation.' job queued successfully';
                    break;
            }

            DB::commit();

            return response()->json([
                'message' => $message,
                'data' => [
                    'festival_id' => $festival->id,
                    'operation' => $operation,
                    'movie_count' => count($movieIds),
                    'processed_at' => now()->toISOString(),
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Bulk operation failed',
                'errors' => ['operation' => ['Failed to process bulk operation: '.$e->getMessage()]],
            ], 500);
        }
    }

    /**
     * Public festival listing (no authentication required)
     */
    public function publicIndex(Request $request): JsonResponse
    {
        $request->validate([
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:50',
            'country' => 'string|max:100',
            'year' => 'integer|min:1900|max:2030',
            'search' => 'string|max:255',
        ]);

        $cacheKey = 'public_festivals:'.md5(serialize($request->only(['page', 'per_page', 'country', 'year', 'search'])));

        $result = Cache::remember($cacheKey, 600, function () use ($request) {
            $query = Festival::where('is_public', true)
                ->where('is_active', true);

            // Apply filters
            if ($request->filled('country')) {
                $query->where('country', 'LIKE', '%'.$request->country.'%');
            }

            if ($request->filled('year')) {
                $query->where('year', $request->year);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('city', 'LIKE', "%{$search}%")
                        ->orWhere('country', 'LIKE', "%{$search}%");
                });
            }

            $query->orderBy('start_date', 'asc');

            $perPage = min($request->get('per_page', 20), 50);

            return $query->select(['id', 'name', 'year', 'city', 'country', 'start_date', 'end_date', 'logo_url', 'website'])
                ->paginate($perPage);
        });

        return response()->json([
            'data' => $result->items(),
            'meta' => [
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage(),
                'per_page' => $result->perPage(),
                'total' => $result->total(),
            ],
        ]);
    }

    /**
     * Public festival detail (no authentication required)
     */
    public function publicShow(Request $request, Festival $festival): JsonResponse
    {
        if (! $festival->is_public || ! $festival->is_active) {
            return response()->json([
                'message' => 'Festival not found or not public',
                'errors' => ['festival' => ['Festival is not publicly accessible']],
            ], 404);
        }

        $cacheKey = "public_festival:{$festival->id}";

        $data = Cache::remember($cacheKey, 600, function () use ($festival) {
            return [
                'id' => $festival->id,
                'name' => $festival->name,
                'edition' => $festival->edition,
                'year' => $festival->year,
                'city' => $festival->city,
                'country' => $festival->country,
                'start_date' => $festival->start_date?->toISOString(),
                'end_date' => $festival->end_date?->toISOString(),
                'description' => $festival->description,
                'website' => $festival->website,
                'logo_url' => $festival->logo_url,
                'banner_url' => $festival->banner_url,
                'allows_submissions' => $festival->allows_submissions,
                'submission_deadline' => $festival->submission_deadline?->toISOString(),
                'movie_count' => $festival->movies()->where('dcp_status', 'completed')->count(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Webhook for festival submissions
     */
    public function webhookSubmission(Request $request): JsonResponse
    {
        // Handle external festival submission webhooks
        // Implementation depends on the external system

        return response()->json(['status' => 'received']);
    }
}
