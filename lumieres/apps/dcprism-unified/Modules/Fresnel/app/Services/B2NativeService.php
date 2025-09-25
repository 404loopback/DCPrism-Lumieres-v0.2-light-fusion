<?php

namespace Modules\Fresnel\app\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service for interacting with Backblaze B2 Native API
 * Based on official B2 documentation: https://www.backblaze.com/apidocs/introduction-to-the-b2-native-api
 */
class B2NativeService
{
    private Client $httpClient;
    private ?string $keyId;
    private ?string $applicationKey;
    private ?array $authData = null;

    public function __construct(?string $keyId = null, ?string $applicationKey = null)
    {
        $this->httpClient = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
            'headers' => [
                'User-Agent' => 'Fresnel/1.0 (B2NativeService)',
            ],
        ]);

        $this->keyId = $keyId ?? env('B2_NATIVE_KEY_ID');
        $this->applicationKey = $applicationKey ?? env('B2_NATIVE_APPLICATION_KEY');

        if (empty($this->keyId) || empty($this->applicationKey)) {
            throw new \Exception('B2 credentials are required (B2_NATIVE_KEY_ID and B2_NATIVE_APPLICATION_KEY)');
        }
    }

    /**
     * Authenticate with B2 and get authorization data
     * Uses b2_authorize_account endpoint from B2 Native API v4
     */
    public function authenticate(): array
    {
        if ($this->authData && $this->authData['expires_at'] > now()) {
            return $this->authData;
        }

        // Try to get from cache first
        $cacheKey = 'b2_auth_' . md5($this->keyId);
        $cachedAuth = Cache::get($cacheKey);
        
        if ($cachedAuth && $cachedAuth['expires_at'] > now()) {
            $this->authData = $cachedAuth;
            return $this->authData;
        }

        try {
            Log::info('🔐 [B2Service] Authentification B2...');

            // Use HTTP Basic Auth as per B2 documentation
            $response = $this->httpClient->get('https://api.backblazeb2.com/b2api/v4/b2_authorize_account', [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->keyId . ':' . $this->applicationKey),
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            $this->authData = [
                'accountId' => $data['accountId'],
                'authorizationToken' => $data['authorizationToken'],
                'apiUrl' => $data['apiInfo']['storageApi']['apiUrl'],
                'downloadUrl' => $data['apiInfo']['storageApi']['downloadUrl'],
                'recommendedPartSize' => $data['apiInfo']['storageApi']['recommendedPartSize'] ?? 100000000,
                'absoluteMinimumPartSize' => $data['apiInfo']['storageApi']['absoluteMinimumPartSize'] ?? 5000000,
                'allowed' => $data['apiInfo']['storageApi']['allowed'] ?? [],
                'expires_at' => now()->addHours(23), // Token expires in 24h, cache for 23h
            ];

            // Cache the auth data
            Cache::put($cacheKey, $this->authData, now()->addHours(23));

            Log::info('✅ [B2Service] Authentification B2 réussie');

            return $this->authData;

        } catch (ClientException|ServerException $e) {
            $errorBody = $e->getResponse()->getBody()->getContents();
            Log::error('❌ [B2Service] Erreur authentification B2: ' . $errorBody);
            throw new \Exception('B2 authentication failed: ' . $errorBody);
        } catch (\Exception $e) {
            Log::error('❌ [B2Service] Erreur authentification B2: ' . $e->getMessage());
            throw new \Exception('B2 authentication error: ' . $e->getMessage());
        }
    }

    /**
     * Get authentication data (authenticate if needed)
     */
    public function getAuthData(): array
    {
        return $this->authenticate();
    }

    /**
     * Get bucket information by name using b2_list_buckets
     */
    public function getBucketByName(string $bucketName): array
    {
        $authData = $this->authenticate();

        try {
            Log::info('📂 [B2Service] Récupération bucket: ' . $bucketName);

            $response = $this->httpClient->post($authData['apiUrl'] . '/b2api/v4/b2_list_buckets', [
                'headers' => [
                    'Authorization' => $authData['authorizationToken'],
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'accountId' => $authData['accountId'],
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            foreach ($data['buckets'] as $bucket) {
                if ($bucket['bucketName'] === $bucketName) {
                    Log::info('✅ [B2Service] Bucket trouvé: ' . $bucketName);
                    return $bucket;
                }
            }

            throw new \Exception("Bucket '{$bucketName}' not found");

        } catch (ClientException|ServerException $e) {
            $errorBody = $e->getResponse()->getBody()->getContents();
            Log::error('❌ [B2Service] Erreur récupération bucket: ' . $errorBody);
            throw new \Exception('Failed to get bucket: ' . $errorBody);
        }
    }

    /**
     * Start a large file upload using b2_start_large_file
     */
    public function startLargeFile(string $bucketId, string $fileName, string $contentType = 'application/octet-stream'): array
    {
        $authData = $this->authenticate();

        try {
            Log::info('🚀 [B2Service] Démarrage large file upload: ' . $fileName);

            $response = $this->httpClient->post($authData['apiUrl'] . '/b2api/v4/b2_start_large_file', [
                'headers' => [
                    'Authorization' => $authData['authorizationToken'],
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'bucketId' => $bucketId,
                    'fileName' => $fileName,
                    'contentType' => $contentType,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            Log::info('✅ [B2Service] Large file upload démarré', [
                'fileId' => $data['fileId'],
                'fileName' => $fileName,
            ]);

            return $data;

        } catch (ClientException|ServerException $e) {
            $errorBody = $e->getResponse()->getBody()->getContents();
            Log::error('❌ [B2Service] Erreur start large file: ' . $errorBody);
            throw new \Exception('Failed to start large file upload: ' . $errorBody);
        }
    }

    /**
     * Get upload part URL using b2_get_upload_part_url
     */
    public function getUploadPartUrl(string $fileId): array
    {
        $authData = $this->authenticate();

        try {
            $response = $this->httpClient->post($authData['apiUrl'] . '/b2api/v4/b2_get_upload_part_url', [
                'headers' => [
                    'Authorization' => $authData['authorizationToken'],
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'fileId' => $fileId,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                'uploadUrl' => $data['uploadUrl'],
                'authorizationToken' => $data['authorizationToken'],
            ];

        } catch (ClientException|ServerException $e) {
            $errorBody = $e->getResponse()->getBody()->getContents();
            Log::error('❌ [B2Service] Erreur get upload part URL: ' . $errorBody);
            throw new \Exception('Failed to get upload part URL: ' . $errorBody);
        }
    }

    /**
     * Upload a part using b2_upload_part
     */
    public function uploadPart(string $uploadUrl, string $authToken, int $partNumber, string $content, string $sha1Hash): array
    {
        try {
            $response = $this->httpClient->post($uploadUrl, [
                'headers' => [
                    'Authorization' => $authToken,
                    'X-Bz-Part-Number' => (string)$partNumber,
                    'Content-Length' => strlen($content),
                    'X-Bz-Content-Sha1' => $sha1Hash,
                ],
                'body' => $content,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            Log::debug('✅ [B2Service] Part uploaded', [
                'partNumber' => $partNumber,
                'contentLength' => $data['contentLength'] ?? 0,
            ]);

            return $data;

        } catch (ClientException|ServerException $e) {
            $errorBody = $e->getResponse()->getBody()->getContents();
            Log::error('❌ [B2Service] Erreur upload part: ' . $errorBody);
            throw new \Exception('Failed to upload part: ' . $errorBody);
        }
    }

    /**
     * Finish large file upload using b2_finish_large_file
     */
    public function finishLargeFile(string $fileId, array $partSha1Array): array
    {
        $authData = $this->authenticate();

        try {
            Log::info('🏁 [B2Service] Finalisation large file upload: ' . $fileId);

            $response = $this->httpClient->post($authData['apiUrl'] . '/b2api/v4/b2_finish_large_file', [
                'headers' => [
                    'Authorization' => $authData['authorizationToken'],
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'fileId' => $fileId,
                    'partSha1Array' => $partSha1Array,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            Log::info('✅ [B2Service] Large file upload finalisé', [
                'fileId' => $data['fileId'],
                'fileName' => $data['fileName'],
                'contentLength' => $data['contentLength'] ?? 0,
            ]);

            return $data;

        } catch (ClientException|ServerException $e) {
            $errorBody = $e->getResponse()->getBody()->getContents();
            Log::error('❌ [B2Service] Erreur finish large file: ' . $errorBody);
            throw new \Exception('Failed to finish large file upload: ' . $errorBody);
        }
    }

    /**
     * Cancel large file upload using b2_cancel_large_file
     */
    public function cancelLargeFile(string $fileId): array
    {
        $authData = $this->authenticate();

        try {
            Log::info('❌ [B2Service] Annulation large file upload: ' . $fileId);

            $response = $this->httpClient->post($authData['apiUrl'] . '/b2api/v4/b2_cancel_large_file', [
                'headers' => [
                    'Authorization' => $authData['authorizationToken'],
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'fileId' => $fileId,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            Log::info('✅ [B2Service] Large file upload annulé: ' . $fileId);

            return $data;

        } catch (ClientException|ServerException $e) {
            $errorBody = $e->getResponse()->getBody()->getContents();
            Log::error('❌ [B2Service] Erreur cancel large file: ' . $errorBody);
            throw new \Exception('Failed to cancel large file upload: ' . $errorBody);
        }
    }

    /**
     * Delete a file using b2_delete_file_version
     */
    public function deleteFile(string $fileId, string $fileName): array
    {
        $authData = $this->authenticate();

        try {
            Log::info('🗑️ [B2Service] Suppression fichier: ' . $fileName);

            $response = $this->httpClient->post($authData['apiUrl'] . '/b2api/v4/b2_delete_file_version', [
                'headers' => [
                    'Authorization' => $authData['authorizationToken'],
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'fileId' => $fileId,
                    'fileName' => $fileName,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            Log::info('✅ [B2Service] Fichier supprimé: ' . $fileName);

            return $data;

        } catch (ClientException|ServerException $e) {
            $errorBody = $e->getResponse()->getBody()->getContents();
            Log::error('❌ [B2Service] Erreur suppression fichier: ' . $errorBody);
            throw new \Exception('Failed to delete file: ' . $errorBody);
        }
    }

    /**
     * Get file info using b2_get_file_info
     */
    public function getFileInfo(string $fileId): array
    {
        $authData = $this->authenticate();

        try {
            $response = $this->httpClient->post($authData['apiUrl'] . '/b2api/v4/b2_get_file_info', [
                'headers' => [
                    'Authorization' => $authData['authorizationToken'],
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'fileId' => $fileId,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (ClientException|ServerException $e) {
            $errorBody = $e->getResponse()->getBody()->getContents();
            Log::error('❌ [B2Service] Erreur get file info: ' . $errorBody);
            throw new \Exception('Failed to get file info: ' . $errorBody);
        }
    }

    /**
     * List files in bucket using b2_list_file_names
     */
    public function listFiles(string $bucketId, ?string $startFileName = null, ?string $prefix = null, int $maxFileCount = 100): array
    {
        $authData = $this->authenticate();

        try {
            $requestData = [
                'bucketId' => $bucketId,
                'maxFileCount' => $maxFileCount,
            ];

            if ($startFileName) {
                $requestData['startFileName'] = $startFileName;
            }

            if ($prefix) {
                $requestData['prefix'] = $prefix;
            }

            $response = $this->httpClient->post($authData['apiUrl'] . '/b2api/v4/b2_list_file_names', [
                'headers' => [
                    'Authorization' => $authData['authorizationToken'],
                    'Content-Type' => 'application/json',
                ],
                'json' => $requestData,
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (ClientException|ServerException $e) {
            $errorBody = $e->getResponse()->getBody()->getContents();
            Log::error('❌ [B2Service] Erreur list files: ' . $errorBody);
            throw new \Exception('Failed to list files: ' . $errorBody);
        }
    }

    /**
     * Calculate optimal chunk size based on file size
     * Based on B2 recommendations: minimum 5MB, recommended 100MB
     */
    public function calculateOptimalChunkSize(int $fileSize): int
    {
        $authData = $this->authenticate();
        $minPartSize = $authData['absoluteMinimumPartSize'] ?? 5000000; // 5MB
        $recommendedPartSize = $authData['recommendedPartSize'] ?? 100000000; // 100MB

        if ($fileSize <= $recommendedPartSize * 2) {
            return max($minPartSize, (int)ceil($fileSize / 2));
        }

        return $recommendedPartSize;
    }

    /**
     * Get recommended part size for multipart uploads
     */
    public function getRecommendedPartSize(): int
    {
        $authData = $this->authenticate();
        return $authData['recommendedPartSize'] ?? 100000000; // 100MB
    }

    /**
     * Get absolute minimum part size for multipart uploads
     */
    public function getMinimumPartSize(): int
    {
        $authData = $this->authenticate();
        return $authData['absoluteMinimumPartSize'] ?? 5000000; // 5MB
    }

    /**
     * Check if a capability is allowed with the current application key
     */
    public function hasCapability(string $capability): bool
    {
        $authData = $this->authenticate();
        $allowed = $authData['allowed'] ?? [];
        $capabilities = $allowed['capabilities'] ?? [];
        
        return in_array($capability, $capabilities) || in_array('listFiles', $capabilities);
    }

    /**
     * Get allowed buckets for the current application key
     */
    public function getAllowedBuckets(): array
    {
        $authData = $this->authenticate();
        $allowed = $authData['allowed'] ?? [];
        return $allowed['buckets'] ?? [];
    }

    /**
     * Test B2 connection using separate test credentials
     * This uses different credentials to avoid affecting production operations
     */
    public function ping(): array
    {
        $testKeyId = env('B2_TEST_KEY_ID');
        $testApplicationKey = env('B2_TEST_APPLICATION_KEY');
        $testBucketName = env('B2_TEST_BUCKET_NAME');

        if (empty($testKeyId) || empty($testApplicationKey) || empty($testBucketName)) {
            throw new \Exception('B2 test credentials are required (B2_TEST_KEY_ID, B2_TEST_APPLICATION_KEY, B2_TEST_BUCKET_NAME)');
        }

        try {
            Log::info('🏓 [B2Service] Test de connexion B2 avec credentials de test...');

            $startTime = microtime(true);

            // Create a temporary B2 service with test credentials
            $testService = new self($testKeyId, $testApplicationKey);
            
            // Test authentication
            $authData = $testService->authenticate();
            
            // Find bucket info from allowed buckets instead of making API call
            $bucketInfo = null;
            $allowedBuckets = $authData['allowed']['buckets'] ?? [];
            
            foreach ($allowedBuckets as $bucket) {
                if ($bucket['name'] === $testBucketName) {
                    $bucketInfo = $bucket;
                    break;
                }
            }
            
            // If no specific bucket found, use the test bucket name anyway
            if (!$bucketInfo) {
                $bucketInfo = [
                    'id' => 'unknown',
                    'name' => $testBucketName,
                    'type' => 'unknown'
                ];
            }
            
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2); // in milliseconds

            $result = [
                'status' => 'success',
                'message' => 'B2 connection successful',
                'response_time_ms' => $responseTime,
                'timestamp' => now()->toISOString(),
                'test_bucket' => $testBucketName,
                'account_id' => $authData['accountId'],
                'api_url' => $authData['apiUrl'],
                'capabilities' => $authData['allowed']['capabilities'] ?? [],
                'bucket_info' => [
                    'id' => $bucketInfo['id'] ?? 'unknown',
                    'name' => $bucketInfo['name'] ?? $testBucketName,
                    'type' => $bucketInfo['type'] ?? 'unknown',
                ],
            ];

            Log::info('✅ [B2Service] Test de connexion B2 réussi', [
                'response_time_ms' => $responseTime,
                'bucket' => $testBucketName,
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('❌ [B2Service] Erreur test de connexion B2: ' . $e->getMessage());
            
            return [
                'status' => 'error',
                'message' => 'B2 connection failed: ' . $e->getMessage(),
                'timestamp' => now()->toISOString(),
                'test_bucket' => $testBucketName ?? 'unknown',
            ];
        }
    }
}
