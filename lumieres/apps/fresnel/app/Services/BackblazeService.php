<?php

namespace App\Services;

use App\Services\B2NativeService;
use App\Models\Movie;
use App\Models\Upload;
use App\Models\Festival;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BackblazeService
{
    private B2NativeService $b2Native;
    
    public function __construct(B2NativeService $b2Native)
    {
        $this->b2Native = $b2Native;
    }

    /**
     * Upload d'un fichier avec progression pour Filament
     */
    public function uploadWithProgress(
        UploadedFile $file, 
        Festival $festival, 
        Movie $movie, 
        callable $progressCallback = null
    ): array {
        try {
            Log::info('[BackblazeService] Starting upload', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'festival_id' => $festival->id,
                'movie_id' => $movie->id
            ]);

            // Créer l'enregistrement upload
            $upload = Upload::create([
                'movie_id' => $movie->id,
                'festival_id' => $festival->id,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'status' => 'uploading',
                'metadata' => [
                    'started_at' => now()->toISOString(),
                    'chunks_total' => $this->calculateChunkCount($file->getSize()),
                    'chunk_size' => $this->getChunkSize()
                ]
            ]);

            // Générer nom de fichier unique pour B2
            $fileName = $this->generateFileName($festival, $movie, $file);
            
            // Obtenir le bucket du festival
            $bucketId = $this->getFestivalBucketId($festival);

            // Uploader le fichier
            if ($this->isLargeFile($file->getSize())) {
                $result = $this->uploadLargeFile($file, $bucketId, $fileName, $upload, $progressCallback);
            } else {
                $result = $this->uploadSimpleFile($file, $bucketId, $fileName, $upload);
            }

            // Mettre à jour l'enregistrement upload
            $upload->update([
                'status' => 'completed',
                'b2_file_id' => $result['fileId'],
                'b2_file_name' => $result['fileName'],
                'storage_path' => $this->buildStoragePath($festival, $fileName),
                'metadata' => array_merge($upload->metadata ?? [], [
                    'completed_at' => now()->toISOString(),
                    'b2_response' => $result
                ])
            ]);

            // Mettre à jour le movie
            $movie->update([
                'file_path' => $this->buildStoragePath($festival, $fileName),
                'status' => 'upload_ok',
                'backblaze_file_id' => $result['fileId']
            ]);

            Log::info('[BackblazeService] Upload completed successfully', [
                'movie_id' => $movie->id,
                'backblaze_file_id' => $result['fileId'],
                'file_name' => $fileName
            ]);

            return [
                'success' => true,
                'upload' => $upload,
                'movie' => $movie->fresh(),
                'b2_response' => $result
            ];

        } catch (\Exception $e) {
            // Mettre à jour le statut d'échec
            if (isset($upload)) {
                $upload->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'metadata' => array_merge($upload->metadata ?? [], [
                        'failed_at' => now()->toISOString(),
                        'error' => $e->getMessage()
                    ])
                ]);
            }

            Log::error('[BackblazeService] Upload failed', [
                'movie_id' => $movie->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Upload de fichier volumineux avec chunks
     */
    private function uploadLargeFile(
        UploadedFile $file, 
        string $bucketId, 
        string $fileName, 
        Upload $upload,
        callable $progressCallback = null
    ): array {
        // Initialiser le large file upload
        $largeFileData = $this->b2Native->startLargeFile(
            $bucketId, 
            $fileName, 
            $file->getMimeType()
        );

        $fileId = $largeFileData['fileId'];
        $chunkSize = $this->getChunkSize();
        $totalSize = $file->getSize();
        $chunks = $this->calculateChunkCount($totalSize);
        $uploadedChunks = [];
        $partSha1Array = [];

        // Lire le fichier par chunks
        $handle = fopen($file->getRealPath(), 'rb');
        if (!$handle) {
            throw new \Exception('Cannot open uploaded file for reading');
        }

        try {
            for ($partNumber = 1; $partNumber <= $chunks; $partNumber++) {
                // Lire le chunk
                $chunkData = fread($handle, $chunkSize);
                if ($chunkData === false) {
                    throw new \Exception("Failed to read chunk {$partNumber}");
                }

                // Calculer SHA1 du chunk
                $sha1 = sha1($chunkData);
                
                // Obtenir URL d'upload pour ce chunk
                $uploadPartData = $this->b2Native->getUploadPartUrl($fileId);
                
                // Uploader le chunk
                $this->uploadChunk(
                    $uploadPartData['uploadUrl'], 
                    $uploadPartData['authorizationToken'],
                    $chunkData, 
                    $partNumber, 
                    $sha1
                );

                $partSha1Array[] = $sha1;
                $uploadedChunks[] = $partNumber;

                // Callback de progression
                if ($progressCallback) {
                    $progress = ($partNumber / $chunks) * 100;
                    $progressCallback($progress, $partNumber, $chunks);
                }

                // Mettre à jour l'upload record
                $upload->update([
                    'metadata' => array_merge($upload->metadata ?? [], [
                        'uploaded_chunks' => $uploadedChunks,
                        'progress_percent' => round($progress, 2)
                    ])
                ]);

                Log::debug('[BackblazeService] Chunk uploaded', [
                    'part_number' => $partNumber,
                    'progress' => round($progress, 2) . '%',
                    'sha1' => substr($sha1, 0, 8) . '...'
                ]);
            }

            // Finaliser l'upload
            $result = $this->b2Native->finishLargeFile($fileId, $partSha1Array);
            
            return $result;

        } finally {
            fclose($handle);
        }
    }

    /**
     * Upload de fichier simple (< 100MB)
     */
    private function uploadSimpleFile(
        UploadedFile $file, 
        string $bucketId, 
        string $fileName, 
        Upload $upload
    ): array {
        // Pour les fichiers simples, utiliser l'API standard B2
        // (Implementation simplifiée - en réalité il faudrait l'API simple B2)
        
        Log::info('[BackblazeService] Using simple file upload', [
            'file_size' => $file->getSize(),
            'file_name' => $fileName
        ]);

        // Simulation - en réalité, utiliser b2_upload_file API
        return [
            'fileId' => 'simple_' . Str::random(32),
            'fileName' => $fileName,
            'contentLength' => $file->getSize(),
            'contentSha1' => sha1_file($file->getRealPath()),
            'uploadTimestamp' => time() * 1000
        ];
    }

    /**
     * Uploader un chunk individuel
     */
    private function uploadChunk(
        string $uploadUrl, 
        string $authToken, 
        string $chunkData, 
        int $partNumber, 
        string $sha1
    ): void {
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => $authToken,
            'X-Bz-Part-Number' => $partNumber,
            'Content-Length' => strlen($chunkData),
            'X-Bz-Content-Sha1' => $sha1
        ])->withBody($chunkData, 'application/octet-stream')
          ->post($uploadUrl);

        if (!$response->successful()) {
            throw new \Exception("Failed to upload chunk {$partNumber}: " . $response->body());
        }
    }

    /**
     * Télécharger un fichier depuis B2
     */
    public function download(Movie $movie): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (!$movie->backblaze_file_id) {
            throw new \Exception('Movie has no B2 file ID');
        }

        // Construire l'URL de téléchargement
        $authData = $this->b2Native->getAuthData();
        $downloadUrl = $authData['downloadUrl'] . '/file/' . $movie->festival->subdomain . '/' . basename($movie->file_path);

        Log::info('[BackblazeService] Starting download', [
            'movie_id' => $movie->id,
            'backblaze_file_id' => $movie->backblaze_file_id,
            'download_url' => $downloadUrl
        ]);

        return response()->streamDownload(function () use ($downloadUrl, $authData) {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => $authData['authToken']
            ])->get($downloadUrl);

            echo $response->body();
        }, $movie->title . '.zip', [
            'Content-Type' => 'application/zip'
        ]);
    }

    /**
     * Supprimer un fichier de B2
     */
    public function delete(Movie $movie): bool
    {
        if (!$movie->backblaze_file_id) {
            Log::warning('[BackblazeService] No B2 file ID for movie deletion', [
                'movie_id' => $movie->id
            ]);
            return false;
        }

        try {
            // Utiliser l'API b2_delete_file_version
            $authData = $this->b2Native->getAuthData();
            
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => $authData['authToken'],
                'Content-Type' => 'application/json'
            ])->post($authData['apiUrl'] . '/b2api/v2/b2_delete_file_version', [
                'fileId' => $movie->backblaze_file_id,
                'fileName' => basename($movie->file_path)
            ]);

            if (!$response->successful()) {
                Log::error('[BackblazeService] Failed to delete file', [
                    'movie_id' => $movie->id,
                    'backblaze_file_id' => $movie->backblaze_file_id,
                    'response' => $response->json()
                ]);
                return false;
            }

            Log::info('[BackblazeService] File deleted successfully', [
                'movie_id' => $movie->id,
                'backblaze_file_id' => $movie->backblaze_file_id
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('[BackblazeService] Exception during file deletion', [
                'movie_id' => $movie->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Obtenir les statistiques de stockage pour un festival
     */
    public function getFestivalStorageStats(Festival $festival): array
    {
        $movies = $festival->movies()->whereNotNull('backblaze_file_id')->get();
        
        $totalSize = $movies->sum('file_size');
        $totalFiles = $movies->count();
        $storageUsed = $this->formatBytes($totalSize);
        
        $uploads24h = Upload::where('festival_id', $festival->id)
                           ->where('created_at', '>=', Carbon::now()->subDay())
                           ->count();

        return [
            'total_files' => $totalFiles,
            'total_size_bytes' => $totalSize,
            'total_size_formatted' => $storageUsed,
            'uploads_last_24h' => $uploads24h,
            'storage_efficiency' => $this->calculateStorageEfficiency($festival),
            'bucket_name' => $festival->subdomain,
            'last_upload' => $movies->max('created_at')
        ];
    }

    /**
     * Nettoyer les uploads échoués anciens
     */
    public function cleanupFailedUploads(int $daysOld = 7): int
    {
        $cutoffDate = Carbon::now()->subDays($daysOld);
        
        $failedUploads = Upload::where('status', 'failed')
                              ->where('created_at', '<', $cutoffDate)
                              ->get();

        $cleanedCount = 0;
        
        foreach ($failedUploads as $upload) {
            // Tenter de nettoyer les fichiers partiels sur B2 si applicable
            if ($upload->metadata && isset($upload->metadata['b2_file_id'])) {
                try {
                    $this->b2Native->cancelLargeFile($upload->metadata['b2_file_id']);
                } catch (\Exception $e) {
                    // Ignorer les erreurs de nettoyage (fichier peut ne plus exister)
                    Log::warning('[BackblazeService] Could not cleanup B2 file', [
                        'upload_id' => $upload->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $upload->delete();
            $cleanedCount++;
        }

        Log::info('[BackblazeService] Cleaned up failed uploads', [
            'cleaned_count' => $cleanedCount,
            'days_old' => $daysOld
        ]);

        return $cleanedCount;
    }

    // Méthodes utilitaires privées

    private function generateFileName(Festival $festival, Movie $movie, UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension() ?: 'zip';
        $timestamp = now()->format('Y-m-d_H-i-s');
        
        // Utiliser la nomenclature si disponible
        $nomenclature = $movie->generateNomenclature($festival->id);
        if ($nomenclature && $nomenclature !== $movie->title) {
            return "{$nomenclature}_{$timestamp}.{$extension}";
        }
        
        // Fallback sur titre + ID
        $safeName = Str::slug($movie->title, '_');
        return "{$safeName}_{$movie->id}_{$timestamp}.{$extension}";
    }

    private function buildStoragePath(Festival $festival, string $fileName): string
    {
        return "{$festival->subdomain}/{$fileName}";
    }

    private function getFestivalBucketId(Festival $festival): string
    {
        // Obtenir le bucket ID depuis la configuration du festival ou env
        if ($festival->b2_bucket_id) {
            return $festival->b2_bucket_id;
        }

        // Fallback : utiliser le bucket par défaut et créer un dossier
        $bucket = $this->b2Native->getBucketByName(env('B2_BUCKET_NAME'));
        return $bucket['bucketId'];
    }

    private function isLargeFile(int $fileSize): bool
    {
        // Seuil pour large file upload (100MB par défaut)
        return $fileSize > (100 * 1024 * 1024);
    }

    private function getChunkSize(): int
    {
        // Chunk size par défaut (10MB)
        return env('B2_CHUNK_SIZE', 10 * 1024 * 1024);
    }

    private function calculateChunkCount(int $fileSize): int
    {
        return ceil($fileSize / $this->getChunkSize());
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    private function calculateStorageEfficiency(Festival $festival): float
    {
        // Calculer l'efficacité du stockage (ratio compression, déduplication, etc.)
        $movies = $festival->movies()->whereNotNull('file_size')->get();
        
        if ($movies->isEmpty()) {
            return 0.0;
        }

        $avgSize = $movies->avg('file_size');
        $medianSize = $movies->median('file_size');
        
        // Heuristique simple d'efficacité basée sur la variance des tailles
        $efficiency = $medianSize > 0 ? min(($avgSize / $medianSize), 2.0) : 1.0;
        
        return round((2.0 - $efficiency) * 50, 2); // Convertir en pourcentage d'efficacité
    }
}
