<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class B2NativeService
{
    private $accountId;
    private $applicationKey;
    private $authToken;
    private $apiUrl;
    private $downloadUrl;

    public function __construct($accountId = null, $applicationKey = null)
    {
        $this->accountId = $accountId ?? env('B2_NATIVE_KEY_ID');
        $this->applicationKey = $applicationKey ?? env('B2_NATIVE_APPLICATION_KEY');
    }

    /**
     * Authentifier avec l'API B2
     */
    public function authorize()
    {
        $response = Http::withBasicAuth($this->accountId, $this->applicationKey)
            ->get('https://api.backblazeb2.com/b2api/v2/b2_authorize_account');

        if (!$response->successful()) {
            throw new \Exception('B2 Authorization failed: ' . $response->body());
        }

        $data = $response->json();
        $this->authToken = $data['authorizationToken'];
        $this->apiUrl = $data['apiUrl'];
        $this->downloadUrl = $data['downloadUrl'];

        Log::info('[B2Native] Authorized successfully', [
            'account_id' => $this->accountId,
            'api_url' => $this->apiUrl
        ]);

        return $this;
    }

    /**
     * Créer un multipart upload B2 natif
     */
    public function startLargeFile($bucketId, $fileName, $contentType = 'application/octet-stream')
    {
        if (!$this->authToken) {
            $this->authorize();
        }

        $response = Http::withHeaders([
            'Authorization' => $this->authToken,
            'Content-Type' => 'application/json'
        ])->post($this->apiUrl . '/b2api/v2/b2_start_large_file', [
            'bucketId' => $bucketId,
            'fileName' => $fileName,
            'contentType' => $contentType
        ]);

        if (!$response->successful()) {
            $errorData = $response->json();
            Log::error('[B2Native] Failed to start large file', [
                'status' => $response->status(),
                'error' => $errorData,
                'bucketId' => $bucketId,
                'fileName' => $fileName
            ]);
            throw new \Exception('Failed to start large file: ' . ($errorData['message'] ?? $response->body()));
        }

        $data = $response->json();
        
        // Validate required fields
        if (!isset($data['fileId'])) {
            throw new \Exception('B2 start_large_file response missing fileId');
        }
        
        Log::info('[B2Native] Large file started successfully', [
            'fileId' => $data['fileId'],
            'fileName' => $fileName
        ]);

        return $data;
    }

    /**
     * Obtenir une URL d'upload pour un chunk (presigned URL B2 native)
     */
    public function getUploadPartUrl($fileId)
    {
        if (!$this->authToken) {
            $this->authorize();
        }

        $response = Http::withHeaders([
            'Authorization' => $this->authToken,
            'Content-Type' => 'application/json'
        ])->post($this->apiUrl . '/b2api/v2/b2_get_upload_part_url', [
            'fileId' => $fileId
        ]);

        if (!$response->successful()) {
            $errorData = $response->json();
            Log::error('[B2Native] Failed to get upload part URL', [
                'status' => $response->status(),
                'error' => $errorData,
                'fileId' => $fileId
            ]);
            throw new \Exception('Failed to get upload part URL: ' . ($errorData['message'] ?? $response->body()));
        }

        $data = $response->json();
        
        // Validate required fields
        if (!isset($data['uploadUrl']) || !isset($data['authorizationToken'])) {
            throw new \Exception('B2 get_upload_part_url response missing required fields');
        }
        
        Log::debug('[B2Native] Upload part URL obtained', [
            'fileId' => $fileId,
            'uploadUrl' => substr($data['uploadUrl'], 0, 50) . '...'
        ]);

        return $data;
    }

    /**
     * Finaliser le large file upload
     */
    public function finishLargeFile($fileId, $partSha1Array)
    {
        if (!$this->authToken) {
            $this->authorize();
        }
        
        // Validate input
        if (empty($partSha1Array) || !is_array($partSha1Array)) {
            throw new \Exception('partSha1Array must be a non-empty array');
        }
        
        // Validate SHA1 format
        foreach ($partSha1Array as $index => $sha1) {
            if (!preg_match('/^[a-f0-9]{40}$/i', $sha1)) {
                throw new \Exception("Invalid SHA1 format at index {$index}: {$sha1}");
            }
        }

        Log::info('[B2Native] Finishing large file', [
            'fileId' => $fileId,
            'partCount' => count($partSha1Array),
            'partSha1Array' => array_map(fn($sha1) => substr($sha1, 0, 8) . '...', $partSha1Array)
        ]);

        $response = Http::withHeaders([
            'Authorization' => $this->authToken,
            'Content-Type' => 'application/json'
        ])->post($this->apiUrl . '/b2api/v2/b2_finish_large_file', [
            'fileId' => $fileId,
            'partSha1Array' => $partSha1Array
        ]);

        if (!$response->successful()) {
            $errorData = $response->json();
            Log::error('[B2Native] Failed to finish large file', [
                'status' => $response->status(),
                'error' => $errorData,
                'fileId' => $fileId,
                'partCount' => count($partSha1Array)
            ]);
            throw new \Exception('Failed to finish large file: ' . ($errorData['message'] ?? $response->body()));
        }

        $data = $response->json();
        
        // Validate required fields
        if (!isset($data['fileId'])) {
            throw new \Exception('B2 finish_large_file response missing fileId');
        }
        
        Log::info('[B2Native] Large file finished successfully', [
            'fileId' => $data['fileId'],
            'fileName' => $data['fileName'] ?? 'unknown',
            'contentLength' => $data['contentLength'] ?? 0
        ]);

        return $data;
    }

    /**
     * Annuler un large file upload
     */
    public function cancelLargeFile($fileId)
    {
        if (!$this->authToken) {
            $this->authorize();
        }

        $response = Http::withHeaders([
            'Authorization' => $this->authToken,
            'Content-Type' => 'application/json'
        ])->post($this->apiUrl . '/b2api/v2/b2_cancel_large_file', [
            'fileId' => $fileId
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to cancel large file: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Obtenir les informations d'un bucket par son nom
     */
    public function getBucketByName($bucketName)
    {
        if (!$this->authToken) {
            $this->authorize();
        }

        $response = Http::withHeaders([
            'Authorization' => $this->authToken,
            'Content-Type' => 'application/json'
        ])->post($this->apiUrl . '/b2api/v2/b2_list_buckets', [
            'accountId' => $this->accountId,
            'bucketName' => $bucketName
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to list buckets: ' . $response->body());
        }

        $buckets = $response->json()['buckets'];
        
        foreach ($buckets as $bucket) {
            if ($bucket['bucketName'] === $bucketName) {
                return $bucket;
            }
        }

        throw new \Exception("Bucket '$bucketName' not found");
    }

    /**
     * Get current authorization data
     */
    public function getAuthData()
    {
        if (!$this->authToken) {
            $this->authorize();
        }

        return [
            'authToken' => $this->authToken,
            'apiUrl' => $this->apiUrl,
            'downloadUrl' => $this->downloadUrl,
            'accountId' => $this->accountId
        ];
    }
}
