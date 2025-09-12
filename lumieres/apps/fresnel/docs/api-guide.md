# DCPrism API Usage Guide

## Overview
DCPrism API provides comprehensive endpoints for Digital Cinema Package (DCP) management, festival submissions, and processing automation. This guide covers the key features and usage patterns.

## Base URL
```
http://localhost:8000/api/v1
```

## Authentication
All API endpoints (except public ones) require authentication using Laravel Sanctum tokens.

### Login
```http
POST /api/v1/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password123",
    "device_name": "My Application"
}
```

**Response:**
```json
{
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com"
        },
        "token": "1|abc123token...",
        "token_type": "Bearer",
        "expires_at": "2024-09-27T10:00:00.000000Z",
        "abilities": ["api:access", "movies:read", "movies:create"]
    }
}
```

### Using the Token
Include the token in all subsequent requests:
```http
Authorization: Bearer 1|abc123token...
```

## Core Resources

### Movies
Movies are the central resource representing DCP content.

#### List Movies
```http
GET /api/v1/movies?page=1&per_page=20&status=completed&sort=-created_at
```

**Query Parameters:**
- `page`: Page number (default: 1)
- `per_page`: Items per page, max 100 (default: 15)
- `sort`: Sort field (`title`, `-title`, `year`, `-year`, `created_at`, `-created_at`, `dcp_size`, `-dcp_size`)
- `status`: Filter by DCP status (`pending`, `uploading`, `processing`, `completed`, `failed`)
- `genre`: Filter by genre
- `year`: Filter by release year
- `festival_id`: Filter by festival ID
- `search`: Search in title, director, synopsis

#### Create Movie
```http
POST /api/v1/movies
Content-Type: application/json

{
    "title": "The Great Movie",
    "original_title": "Le Grand Film",
    "director": "John Director",
    "year": 2024,
    "duration": 7200,
    "genre": "Drama",
    "rating": "PG-13",
    "synopsis": "A compelling story...",
    "poster_url": "https://example.com/poster.jpg",
    "trailer_url": "https://example.com/trailer.mp4"
}
```

#### Get Movie Details
```http
GET /api/v1/movies/{id}
```

#### Get DCP Status
```http
GET /api/v1/movies/{id}/dcp-status
```

**Response includes:**
- DCP processing status and progress
- Active and recent processing jobs
- Validation status
- Download availability

#### Upload DCP File
```http
POST /api/v1/movies/{id}/upload-dcp
Content-Type: multipart/form-data

dcp_file: [ZIP/TAR file, max 50GB]
```

### Festivals
Manage film festivals and submissions.

#### List Festivals
```http
GET /api/v1/festivals?status=open&country=France&year=2024
```

#### Create Festival
```http
POST /api/v1/festivals
Content-Type: application/json

{
    "name": "Cannes Film Festival",
    "edition": "77th",
    "year": 2024,
    "city": "Cannes",
    "country": "France",
    "start_date": "2024-05-14",
    "end_date": "2024-05-25",
    "description": "The most prestigious film festival...",
    "website": "https://www.festival-cannes.fr",
    "contact_email": "contact@festival-cannes.fr",
    "allows_submissions": true,
    "submission_deadline": "2024-03-15"
}
```

#### Get Festival Movies
```http
GET /api/v1/festivals/{id}/movies?status=completed
```

#### Attach Movie to Festival
```http
POST /api/v1/festivals/{festival_id}/movies/{movie_id}
```

#### Get Festival Statistics
```http
GET /api/v1/festivals/{id}/statistics
```

### Jobs
Monitor and manage background processing jobs.

#### List Jobs
```http
GET /api/v1/jobs?status=processing&job_type=DcpAnalysisJob&date_from=2024-08-01
```

#### Get Job Details
```http
GET /api/v1/jobs/{id}
```

#### Retry Failed Job
```http
POST /api/v1/jobs/{id}/retry
```

#### Cancel Job
```http
DELETE /api/v1/jobs/{id}/cancel
```

#### Get Job Logs
```http
GET /api/v1/jobs/{id}/logs?lines=100&level=error
```

#### Job Statistics
```http
GET /api/v1/jobs/statistics/overview?period=day&include_performance=true
```

## DCP Processing
Advanced DCP processing operations.

### Start DCP Analysis
```http
POST /api/v1/processing/analyze
Content-Type: application/json

{
    "movie_id": 123,
    "options": {
        "deep_scan": true,
        "extract_thumbnails": true,
        "validate_structure": true
    },
    "priority": "high",
    "callback_url": "https://myapp.com/webhooks/analysis"
}
```

### Start DCP Validation
```http
POST /api/v1/processing/validate
Content-Type: application/json

{
    "movie_id": 123,
    "validation_rules": ["dci_compliance", "audio_levels", "subtitle_format"],
    "strict_mode": true,
    "callback_url": "https://myapp.com/webhooks/validation"
}
```

### Extract Metadata
```http
POST /api/v1/processing/extract-metadata
Content-Type: application/json

{
    "movie_id": 123,
    "extract_options": {
        "include_technical": true,
        "include_assets": true,
        "include_subtitles": true
    }
}
```

### Batch Processing
```http
POST /api/v1/processing/batch-process
Content-Type: application/json

{
    "movie_ids": [123, 124, 125],
    "operation": "validate",
    "options": {
        "strict_mode": false
    },
    "callback_url": "https://myapp.com/webhooks/batch"
}
```

## Chunked Upload
For large DCP files, use the chunked upload system.

### 1. Initialize Upload
```http
POST /api/v1/processing/upload/initialize
Content-Type: application/json

{
    "movie_id": 123,
    "filename": "movie-dcp.zip",
    "file_size": 52428800000,
    "chunk_size": 5242880,
    "content_type": "application/zip"
}
```

**Response:**
```json
{
    "message": "Upload initialized successfully",
    "data": {
        "upload_id": "uuid-123",
        "movie_id": 123,
        "total_chunks": 10000,
        "chunk_size": 5242880,
        "expires_at": "2024-08-28T11:00:00.000000Z"
    }
}
```

### 2. Upload Chunks
```http
POST /api/v1/processing/upload/chunk
Content-Type: multipart/form-data

upload_id: uuid-123
chunk_number: 1
chunk_data: [binary data]
```

### 3. Complete Upload
```http
POST /api/v1/processing/upload/complete
Content-Type: application/json

{
    "upload_id": "uuid-123"
}
```

### 4. Check Progress
```http
GET /api/v1/processing/upload/{upload_id}/progress
```

## Error Handling

### Standard Error Response
```json
{
    "message": "Validation failed",
    "errors": {
        "title": ["The title field is required."],
        "year": ["The year must be between 1900 and 2030."]
    }
}
```

### HTTP Status Codes
- `200`: Success
- `201`: Created
- `202`: Accepted (async operation started)
- `400`: Bad Request
- `401`: Unauthorized
- `403`: Forbidden
- `404`: Not Found
- `422`: Validation Error
- `429`: Rate Limited
- `500`: Server Error

## Rate Limiting
Different endpoints have different rate limits:
- **API endpoints**: 1000 requests/minute for authenticated users
- **Public endpoints**: 60 requests/minute per IP
- **Upload endpoints**: 10 requests/minute per user
- **Processing endpoints**: 20 requests/minute per user

## Webhooks
For long-running operations, you can provide callback URLs to receive notifications.

### Webhook Format
```json
{
    "job_id": "uuid-123",
    "status": "completed",
    "progress": 100,
    "message": "Processing completed successfully",
    "result": {
        "metadata_extracted": true,
        "validation_passed": true,
        "issues_found": []
    }
}
```

### Webhook Security
Webhooks are signed with HMAC-SHA256. Verify the signature using the `X-DCPrism-Signature` header.

## Best Practices

### 1. Pagination
Always use pagination for list endpoints:
```http
GET /api/v1/movies?page=1&per_page=50
```

### 2. Filtering
Use specific filters to reduce response size:
```http
GET /api/v1/movies?status=completed&genre=Drama&year=2024
```

### 3. Conditional Includes
Request additional data only when needed:
```http
GET /api/v1/movies?include_metadata=true&include_errors=false
```

### 4. Error Handling
Always check status codes and handle errors appropriately:
```javascript
if (response.status === 422) {
    // Handle validation errors
    console.log(response.data.errors);
} else if (response.status === 429) {
    // Handle rate limiting
    console.log('Rate limited, retry after:', response.headers['retry-after']);
}
```

### 5. Long-Running Operations
Use webhooks for long-running operations instead of polling:
```javascript
// Instead of polling
setInterval(() => checkJobStatus(jobId), 5000);

// Use webhooks
startProcessing({
    movie_id: 123,
    callback_url: 'https://myapp.com/webhooks/processing'
});
```

## SDK Examples

### JavaScript/Node.js
```javascript
const DCPrismAPI = require('@dcprism/api-client');

const client = new DCPrismAPI({
    baseURL: 'http://localhost:8000/api/v1',
    token: 'your-bearer-token'
});

// List movies
const movies = await client.movies.list({
    status: 'completed',
    per_page: 20
});

// Create movie
const movie = await client.movies.create({
    title: 'New Movie',
    director: 'Director Name',
    year: 2024,
    genre: 'Drama'
});

// Start processing
await client.processing.analyze({
    movie_id: movie.id,
    options: { deep_scan: true }
});
```

### Python
```python
from dcprism_api import DCPrismClient

client = DCPrismClient(
    base_url="http://localhost:8000/api/v1",
    token="your-bearer-token"
)

# List movies
movies = client.movies.list(status="completed", per_page=20)

# Create movie
movie = client.movies.create({
    "title": "New Movie",
    "director": "Director Name", 
    "year": 2024,
    "genre": "Drama"
})

# Start processing
client.processing.analyze({
    "movie_id": movie["id"],
    "options": {"deep_scan": True}
})
```

This guide provides a comprehensive overview of the DCPrism API. For complete API documentation with interactive testing, visit `/api/documentation` on your DCPrism instance.
