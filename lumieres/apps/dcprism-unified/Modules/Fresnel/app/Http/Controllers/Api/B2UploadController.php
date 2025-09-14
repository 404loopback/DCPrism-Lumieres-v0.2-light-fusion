<?php

namespace Modules\Fresnel\app\Http\Controllers\Api;

use Modules\Fresnel\app\Http\Controllers\Controller;
use Modules\Fresnel\app\Models\Movie;
use Modules\Fresnel\app\Models\Upload;
use Modules\Fresnel\app\Services\B2NativeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class B2UploadController extends Controller
{
    private B2NativeService $b2Service;

    public function __construct()
    {
        $this->b2Service = new B2NativeService();
    }

    /**
     * Get B2 credentials and bucket info for frontend
     */
    public function getB2Credentials(Request $request)
    {
        try {
            Log::info('🌐 [B2Upload] Demande credentials B2 native');
            
            $user = Auth::user();
            
            // Get B2 credentials (priority: user specific, then environment)
            $keyId = $user->b2_key ?? env('B2_NATIVE_KEY_ID');
            $applicationKey = $user->b2_secret ?? env('B2_NATIVE_APPLICATION_KEY');
            $bucketName = env('B2_BUCKET_NAME', 'dcp-test');
            
            if (empty($keyId) || empty($applicationKey)) {
                throw new \Exception('Missing B2 credentials');
            }

            // Initialize B2 service with user credentials
            $b2Service = new B2NativeService($keyId, $applicationKey);
            $authData = $b2Service->getAuthData();
            
            // Get bucket info
            $bucket = $b2Service->getBucketByName($bucketName);
            
            Log::info('✅ [B2Upload] Credentials B2 obtenues avec succès');
            
            return response()->json([
                'success' => true,
                'data' => [
                    'apiUrl' => $authData['apiUrl'],
                    'authToken' => $authData['authToken'],
                    'bucketId' => $bucket['bucketId'],
                    'bucketName' => $bucket['bucketName'],
                    'accountId' => $authData['accountId'],
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ [B2Upload] Erreur credentials: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur récupération credentials B2: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate upload path based on movie and nomenclature
     */
    public function generateUploadPath(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'movie_id' => 'required|exists:movies,id',
                'filename' => 'required|string|max:255',
                'file_size' => 'required|integer|min:1',
                'mime_type' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $movieId = $request->input('movie_id');
            $filename = $request->input('filename');
            $fileSize = $request->input('file_size');
            $mimeType = $request->input('mime_type');
            
            // Get movie with festival for nomenclature
            $movie = Movie::with('festival')->findOrFail($movieId);
            
            // Generate upload path using nomenclature logic
            $uploadPath = $this->generateNomenclaturePath($movie, $filename);
            
            Log::info('📂 [B2Upload] Chemin généré', [
                'movie_id' => $movieId,
                'upload_path' => $uploadPath,
                'file_size' => $fileSize
            ]);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'upload_path' => $uploadPath,
                    'movie_id' => $movieId,
                    'filename' => $filename,
                    'file_size' => $fileSize,
                    'mime_type' => $mimeType,
                    'bucket_name' => env('B2_BUCKET_NAME', 'dcp-test')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ [B2Upload] Erreur génération chemin: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur génération chemin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initialize multipart upload
     */
    public function initializeMultipart(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'movie_id' => 'required|exists:movies,id',
                'upload_path' => 'required|string',
                'filename' => 'required|string',
                'file_size' => 'required|integer|min:1',
                'mime_type' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $movieId = $request->input('movie_id');
            $uploadPath = $request->input('upload_path');
            $filename = $request->input('filename');
            $fileSize = $request->input('file_size');
            $mimeType = $request->input('mime_type');
            
            // Get B2 bucket info
            $bucketName = env('B2_BUCKET_NAME', 'dcp-test');
            $bucket = $this->b2Service->getBucketByName($bucketName);
            
            // Start large file upload
            $result = $this->b2Service->startLargeFile(
                $bucket['bucketId'],
                $uploadPath,
                $mimeType
            );
            
            // Calculate optimal chunk size and total parts
            $chunkSize = $this->calculateOptimalChunkSize($fileSize);
            $totalParts = ceil($fileSize / $chunkSize);
            
            // Create upload record
            $upload = Upload::create([
                'movie_id' => $movieId,
                'original_filename' => $filename,
                'file_path' => $uploadPath,
                'bucket_name' => $bucketName,
                'file_size' => $fileSize,
                'file_type' => pathinfo($filename, PATHINFO_EXTENSION),
                'mime_type' => $mimeType,
                'status' => 'uploading',
                'upload_id' => $result['fileId'],
                'total_parts' => $totalParts,
                'expires_at' => now()->addDay(), // 24h TTL
                'user_id' => Auth::id(),
                'started_at' => now(),
                'metadata' => [
                    'chunk_size' => $chunkSize,
                    'b2_file_id' => $result['fileId'],
                ]
            ]);
            
            Log::info('🚀 [B2Upload] Multipart initialisé', [
                'upload_id' => $upload->id,
                'b2_file_id' => $result['fileId'],
                'total_parts' => $totalParts,
                'chunk_size' => $chunkSize
            ]);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'upload_id' => $upload->id,
                    'b2_file_id' => $result['fileId'],
                    'total_parts' => $totalParts,
                    'chunk_size' => $chunkSize,
                    'expires_at' => $upload->expires_at->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ [B2Upload] Erreur initialisation multipart: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur initialisation multipart: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get presigned URL for chunk upload
     */
    public function getPresignedUrl(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'upload_id' => 'required|exists:uploads,id',
                'part_number' => 'required|integer|min:1|max:10000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $uploadId = $request->input('upload_id');
            $partNumber = $request->input('part_number');
            
            // Get upload record
            $upload = Upload::findOrFail($uploadId);
            
            // Check if upload belongs to current user
            if ($upload->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            // Check if upload is still valid
            if (!$upload->isResumable()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload expired or invalid'
                ], 410);
            }
            
            // Get presigned URL from B2
            $result = $this->b2Service->getUploadPartUrl($upload->upload_id);
            
            Log::debug('🔗 [B2Upload] URL présignée générée', [
                'upload_id' => $uploadId,
                'part_number' => $partNumber
            ]);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'upload_url' => $result['uploadUrl'],
                    'authorization_token' => $result['authorizationToken'],
                    'part_number' => $partNumber,
                    'expires_in' => 1200, // 20 minutes
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ [B2Upload] Erreur URL présignée: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur génération URL présignée: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete multipart upload
     */
    public function completeMultipart(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'upload_id' => 'required|exists:uploads,id',
                'part_sha1_array' => 'required|array',
                'part_sha1_array.*' => 'required|string|regex:/^[a-f0-9]{40}$/i'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $uploadId = $request->input('upload_id');
            $partSha1Array = $request->input('part_sha1_array');
            
            // Get upload record
            $upload = Upload::findOrFail($uploadId);
            
            // Check ownership
            if ($upload->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            // Complete B2 large file
            $result = $this->b2Service->finishLargeFile(
                $upload->upload_id,
                $partSha1Array
            );
            
            // Update upload record
            $upload->update([
                'status' => 'completed',
                'completed_at' => now(),
                'progress_percentage' => 100,
                'uploaded_bytes' => $upload->file_size,
                'part_sha1_array' => $partSha1Array,
                'completed_parts' => count($partSha1Array),
                'metadata' => array_merge($upload->metadata ?? [], [
                    'b2_result' => $result
                ])
            ]);
            
            Log::info('✅ [B2Upload] Multipart complété', [
                'upload_id' => $uploadId,
                'b2_file_id' => $result['fileId'],
                'content_length' => $result['contentLength'] ?? 0
            ]);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'upload_id' => $uploadId,
                    'file_id' => $result['fileId'],
                    'file_name' => $result['fileName'],
                    'content_length' => $result['contentLength'] ?? 0,
                    'file_path' => $upload->file_path
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ [B2Upload] Erreur completion multipart: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur completion multipart: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel/abort multipart upload
     */
    public function abortMultipart(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'upload_id' => 'required|exists:uploads,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $uploadId = $request->input('upload_id');
            
            // Get upload record
            $upload = Upload::findOrFail($uploadId);
            
            // Check ownership
            if ($upload->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            // Cancel B2 large file if it exists
            if ($upload->upload_id) {
                try {
                    $this->b2Service->cancelLargeFile($upload->upload_id);
                } catch (\Exception $e) {
                    Log::warning('⚠️ [B2Upload] Erreur annulation B2 (peut être normal): ' . $e->getMessage());
                }
            }
            
            // Update upload record
            $upload->update([
                'status' => 'cancelled',
                'error_message' => 'Cancelled by user'
            ]);
            
            Log::info('🛑 [B2Upload] Multipart annulé', [
                'upload_id' => $uploadId,
                'b2_file_id' => $upload->upload_id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Upload annulé avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('❌ [B2Upload] Erreur annulation multipart: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur annulation multipart: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update upload progress
     */
    public function updateProgress(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'upload_id' => 'required|exists:uploads,id',
                'completed_parts' => 'required|integer|min:0',
                'uploaded_bytes' => 'required|integer|min:0',
                'upload_speed_mbps' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $uploadId = $request->input('upload_id');
            $completedParts = $request->input('completed_parts');
            $uploadedBytes = $request->input('uploaded_bytes');
            $uploadSpeedMbps = $request->input('upload_speed_mbps');
            
            // Get upload record
            $upload = Upload::findOrFail($uploadId);
            
            // Check ownership
            if ($upload->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            // Calculate progress percentage
            $progressPercentage = $upload->total_parts > 0 
                ? ($completedParts / $upload->total_parts) * 100
                : 0;
            
            // Update upload record
            $upload->update([
                'completed_parts' => $completedParts,
                'uploaded_bytes' => $uploadedBytes,
                'upload_speed_mbps' => $uploadSpeedMbps,
                'progress_percentage' => $progressPercentage
            ]);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'progress_percentage' => $progressPercentage,
                    'estimated_time_remaining' => $upload->estimated_time_remaining
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ [B2Upload] Erreur mise à jour progression: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur mise à jour progression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get resumable uploads for current user
     */
    public function getResumableUploads(Request $request)
    {
        try {
            $uploads = Upload::where('user_id', Auth::id())
                ->resumable()
                ->with('movie')
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $uploads->map(function ($upload) {
                    return [
                        'id' => $upload->id,
                        'movie_title' => $upload->movie->title ?? 'Unknown',
                        'filename' => $upload->original_filename,
                        'file_size' => $upload->file_size,
                        'formatted_size' => $upload->formatted_size,
                        'progress_percentage' => $upload->progress_percentage,
                        'completed_parts' => $upload->completed_parts,
                        'total_parts' => $upload->total_parts,
                        'upload_speed_mbps' => $upload->upload_speed_mbps,
                        'estimated_time_remaining' => $upload->estimated_time_remaining,
                        'created_at' => $upload->created_at->toISOString(),
                        'expires_at' => $upload->expires_at->toISOString(),
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('❌ [B2Upload] Erreur récupération uploads reprenables: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur récupération uploads: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate nomenclature-based upload path
     */
    private function generateNomenclaturePath(Movie $movie, string $filename): string
    {
        $festival = $movie->festival;
        $year = $festival ? $festival->year : date('Y');
        $festivalSlug = $festival ? Str::slug($festival->name) : 'unknown';
        $movieSlug = Str::slug($movie->title ?: 'untitled');
        
        // Generate path: festival/year/movie/filename
        return sprintf(
            '%s/%d/%s/%s',
            $festivalSlug,
            $year,
            $movieSlug,
            $filename
        );
    }

    /**
     * Calculate optimal chunk size based on file size
     */
    private function calculateOptimalChunkSize(int $fileSize): int
    {
        $MB = 1024 * 1024;
        $GB = $MB * 1024;
        
        if ($fileSize < $GB) {
            // Small files: 100MB chunks
            return 100 * $MB;
        } elseif ($fileSize < (10 * $GB)) {
            // Medium files: 200MB chunks
            return 200 * $MB;
        } else {
            // Large files: 500MB chunks
            return 500 * $MB;
        }
    }
}
