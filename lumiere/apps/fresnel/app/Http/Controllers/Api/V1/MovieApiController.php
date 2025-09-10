<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\MovieResource;
use App\Http\Resources\V1\MovieCollection;
use App\Models\Movie;
use App\Repositories\MovieRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Jobs\DcpAnalysisJob;
use App\Jobs\DcpValidationJob;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Traits\{HasValidation, HasLogging};
use Exception;

class MovieApiController extends Controller
{
    use HasValidation, HasLogging;

    protected MovieRepository $movieRepository;

    public function __construct(
        MovieRepository $movieRepository
    ) {
        $this->movieRepository = $movieRepository;
    }
    /**
     * Display a paginated listing of movies
     *
     * @OA\Get(
     *     path="/api/v1/movies",
     *     summary="List movies",
     *     description="Get paginated list of movies with filtering and sorting options",
     *     tags={"Movies"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page (max 100)",
     *         @OA\Schema(type="integer", minimum=1, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort field and direction",
     *         @OA\Schema(type="string", enum={"title", "-title", "year", "-year", "created_at", "-created_at", "dcp_size", "-dcp_size"})
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by DCP status",
     *         @OA\Schema(type="string", enum={"pending", "uploading", "processing", "completed", "failed"})
     *     ),
     *     @OA\Parameter(
     *         name="genre",
     *         in="query",
     *         description="Filter by genre",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="query",
     *         description="Filter by release year",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="festival_id",
     *         in="query",
     *         description="Filter by festival ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in title, director, synopsis",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Movies retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Movie")),
     *             @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        // Utiliser la validation du contrôleur de base
        $validated = $this->validateRequest($request, [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'sort' => 'string|in:title,-title,year,-year,created_at,-created_at,dcp_size,-dcp_size',
            'status' => 'string|in:pending,uploading,processing,completed,failed',
            'genre' => 'string|max:50',
            'year' => 'integer|min:1900|max:2030',
            'festival_id' => 'integer|exists:festivals,id',
            'search' => 'string|max:255',
            'include_metadata' => 'boolean',
            'include_errors' => 'boolean',
        ]);

        // Logger la requête avec contexte
        $this->logInfo('Movies index requested', [
            'user_id' => $request->user()->id ?? 'guest',
            'filters' => array_filter($validated),
            'ip' => $request->ip()
        ]);

        try {
            // Utiliser le repository pour la recherche avancée
            $movies = $this->movieRepository->searchAndFilter($validated, [
                'per_page' => $validated['per_page'] ?? 15,
                'sort' => $validated['sort'] ?? '-created_at'
            ]);

            $this->logInfo('Movies retrieved successfully', [
                'count' => $movies->count(),
                'total' => $movies->total(),
            ]);

            return $this->paginatedResponse($movies, MovieCollection::class);

        } catch (Exception $e) {
            $this->logError('Failed to retrieve movies', [
                'error' => $e->getMessage(),
                'filters' => $validated
            ]);

            return $this->errorResponse('Failed to retrieve movies', 500);
        }
    }

    /**
     * Store a newly created movie
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Movie::class);

        $validated = $this->validateRequest($request, [
            'title' => 'required|string|max:255',
            'original_title' => 'nullable|string|max:255',
            'director' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:2030',
            'duration' => 'nullable|integer|min:1',
            'genre' => 'required|string|max:100',
            'rating' => 'nullable|string|max:10',
            'synopsis' => 'nullable|string',
            'poster_url' => 'nullable|url',
            'trailer_url' => 'nullable|url',
        ]);

        $this->logInfo('Creating new movie', [
            'title' => $validated['title'],
            'user_id' => $request->user()->id
        ]);

        try {
            $movie = $this->movieRepository->create(array_merge($validated, [
                'created_by' => $request->user()->id,
                'dcp_status' => 'pending',
                'processing_status' => 'pending',
            ]));

            $this->logInfo('Movie created successfully', [
                'movie_id' => $movie->id,
                'title' => $movie->title
            ]);

            return $this->successResponse(
                'Movie created successfully',
                new MovieResource($movie->load('festivals', 'processingJobs')),
                201
            );

        } catch (Exception $e) {
            $this->logError('Failed to create movie', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            return $this->errorResponse('Failed to create movie', 500);
        }
    }

    /**
     * Display the specified movie
     */
    public function show(Request $request, Movie $movie): JsonResponse
    {
        $this->authorize('view', $movie);

        $this->logInfo('Movie show requested', [
            'movie_id' => $movie->id,
            'user_id' => $request->user()->id ?? 'guest'
        ]);

        try {
            $movie = $this->movieRepository->findWithRelations($movie->id, [
                'festivals',
                'processingJobs' => function($q) {
                    $q->latest()->limit(10);
                }
            ]);

            return $this->successResponse(
                null,
                new MovieResource($movie)
            );

        } catch (Exception $e) {
            $this->logError('Failed to retrieve movie details', [
                'movie_id' => $movie->id,
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse('Movie not found', 404);
        }
    }

    /**
     * Update the specified movie
     */
    public function update(Request $request, Movie $movie): JsonResponse
    {
        $this->authorize('update', $movie);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'original_title' => 'nullable|string|max:255',
            'director' => 'sometimes|string|max:255',
            'year' => 'sometimes|integer|min:1900|max:2030',
            'duration' => 'nullable|integer|min:1',
            'genre' => 'sometimes|string|max:100',
            'rating' => 'nullable|string|max:10',
            'synopsis' => 'nullable|string',
            'poster_url' => 'nullable|url',
            'trailer_url' => 'nullable|url',
        ]);

        $movie->update($validated);

        return response()->json([
            'message' => 'Movie updated successfully',
            'data' => new MovieResource($movie->load('festivals', 'processingJobs'))
        ]);
    }

    /**
     * Remove the specified movie
     */
    public function destroy(Movie $movie): JsonResponse
    {
        $this->authorize('delete', $movie);

        // Cancel any running jobs
        $movie->processingJobs()
            ->whereIn('status', ['pending', 'processing'])
            ->update(['status' => 'cancelled']);

        // Delete DCP file if exists
        if ($movie->dcp_path && Storage::exists($movie->dcp_path)) {
            Storage::delete($movie->dcp_path);
        }

        $movie->delete();

        return response()->json([
            'message' => 'Movie deleted successfully'
        ]);
    }

    /**
     * Get DCP status and detailed information
     */
    public function dcpStatus(Request $request, Movie $movie): JsonResponse
    {
        $this->authorize('view', $movie);

        $activeJobs = $movie->processingJobs()
            ->whereIn('status', ['pending', 'processing'])
            ->get();

        $recentJobs = $movie->processingJobs()
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'data' => [
                'movie_id' => $movie->id,
                'dcp_status' => $movie->dcp_status,
                'processing_status' => $movie->processing_status,
                'processing_progress' => $movie->processing_progress,
                'dcp_size' => $movie->dcp_size,
                'dcp_checksum' => $movie->dcp_checksum,
                'dcp_created_at' => $movie->dcp_created_at?->toISOString(),
                'dcp_validated_at' => $movie->dcp_validated_at?->toISOString(),
                'last_processed_at' => $movie->last_processed_at?->toISOString(),
                'active_jobs' => [],
                'recent_jobs' => [],
                'validation_status' => [
                    'is_valid' => $movie->dcp_validated_at !== null && $movie->processing_errors === null,
                    'has_dcp' => $movie->dcp_status === 'completed',
                    'has_metadata' => $movie->technical_metadata !== null,
                    'checksum_verified' => $movie->dcp_checksum !== null,
                ],
                'can_download' => $movie->dcp_status === 'completed' && 
                                 $movie->dcp_path && 
                                 Storage::exists($movie->dcp_path) &&
                                 $request->user()->can('download', $movie)
            ]
        ]);
    }

    /**
     * Upload DCP file
     */
    public function uploadDcp(Request $request, Movie $movie): JsonResponse
    {
        $this->authorize('update', $movie);

        $request->validate([
            'dcp_file' => 'required|file|mimes:zip,tar,gz|max:51200000', // 50GB max
        ]);

        try {
            $file = $request->file('dcp_file');
            $path = $file->store("dcp/movies/{$movie->id}", 'public');
            
            $movie->update([
                'dcp_path' => $path,
                'dcp_size' => $file->getSize(),
                'dcp_status' => 'uploaded',
                'upload_status' => 'completed',
                'uploaded_at' => now(),
                'uploaded_by' => $request->user()->id,
            ]);

            // Trigger analysis job
            DcpAnalysisJob::dispatch($movie)->onQueue('dcp-analysis');

            return response()->json([
                'message' => 'DCP uploaded successfully and analysis started',
                'data' => new MovieResource($movie)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Upload failed',
                'errors' => ['file' => ['Failed to upload DCP file']]
            ], 500);
        }
    }

    /**
     * Download DCP file
     */
    public function downloadDcp(Request $request, Movie $movie): StreamedResponse|JsonResponse
    {
        $this->authorize('download', $movie);

        if ($movie->dcp_status !== 'completed' || !$movie->dcp_path) {
            return response()->json([
                'message' => 'DCP not available for download',
                'errors' => ['dcp' => ['DCP file is not ready or does not exist']]
            ], 404);
        }

        if (!Storage::exists($movie->dcp_path)) {
            return response()->json([
                'message' => 'DCP file not found',
                'errors' => ['dcp' => ['DCP file not found on storage']]
            ], 404);
        }

        $filename = "{$movie->title} ({$movie->year}) - DCP.zip";
        
        return Storage::download($movie->dcp_path, $filename);
    }

    /**
     * Validate DCP
     */
    public function validate(Request $request, Movie $movie): JsonResponse
    {
        $this->authorize('validate', $movie);

        if ($movie->dcp_status !== 'completed') {
            return response()->json([
                'message' => 'Cannot validate DCP',
                'errors' => ['dcp' => ['DCP must be completed before validation']]
            ], 400);
        }

        // Dispatch validation job
        $job = DcpValidationJob::dispatch($movie)->onQueue('dcp-validation');

        return response()->json([
            'message' => 'DCP validation started',
            'data' => [
                'movie_id' => $movie->id,
                'validation_started' => true,
                'estimated_duration' => '5-15 minutes'
            ]
        ]);
    }

    /**
     * Get movie metadata
     */
    public function metadata(Request $request, Movie $movie): JsonResponse
    {
        $this->authorize('view', $movie);

        $metadata = $movie->technical_metadata 
            ? json_decode($movie->technical_metadata, true) 
            : null;

        return response()->json([
            'data' => [
                'movie_id' => $movie->id,
                'has_metadata' => $metadata !== null,
                'metadata' => $metadata,
                'extracted_at' => $movie->metadata_extracted_at?->toISOString(),
                'file_info' => [
                    'size' => $movie->dcp_size,
                    'size_human' => $movie->dcp_size ? $this->formatBytes($movie->dcp_size) : null,
                    'checksum' => $movie->dcp_checksum,
                    'created_at' => $movie->dcp_created_at?->toISOString(),
                ]
            ]
        ]);
    }

    /**
     * Get processing history
     */
    public function processingHistory(Request $request, Movie $movie): JsonResponse
    {
        $this->authorize('view', $movie);

        $jobs = $movie->processingJobs()
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => [],
            'meta' => [
                'current_page' => $jobs->currentPage(),
                'last_page' => $jobs->lastPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
            ]
        ]);
    }

    /**
     * Public search endpoint (no authentication required)
     */
    public function publicSearch(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'limit' => 'integer|min:1|max:50'
        ]);

        $cacheKey = 'movie_search:' . md5($request->q . ':' . ($request->limit ?? 10));
        
        $results = Cache::remember($cacheKey, 300, function () use ($request) {
            return Movie::where('dcp_status', 'completed')
                ->where(function ($q) use ($request) {
                    $search = $request->q;
                    $q->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('director', 'LIKE', "%{$search}%")
                      ->orWhere('genre', 'LIKE', "%{$search}%");
                })
                ->select(['id', 'title', 'director', 'year', 'genre', 'poster_url'])
                ->limit($request->get('limit', 10))
                ->get();
        });

        return response()->json([
            'data' => $results
        ]);
    }

    /**
     * Webhook for upload notifications
     */
    public function webhookUploadNotification(Request $request): JsonResponse
    {
        // This would handle webhooks from external upload services
        // Implementation depends on the specific service used
        
        return response()->json(['status' => 'received']);
    }

}
