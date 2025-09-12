<?php

/**
 * OpenAPI Schemas for DCPrism API
 * 
 * @OA\Schema(
 *     schema="ApiResponse",
 *     @OA\Property(property="message", type="string", description="Response message"),
 *     @OA\Property(property="data", type="object", description="Response data"),
 *     @OA\Property(property="errors", type="object", description="Validation errors", nullable=true),
 * )
 * 
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     @OA\Property(property="current_page", type="integer", description="Current page number"),
 *     @OA\Property(property="last_page", type="integer", description="Last page number"),
 *     @OA\Property(property="per_page", type="integer", description="Items per page"),
 *     @OA\Property(property="total", type="integer", description="Total items"),
 *     @OA\Property(property="from", type="integer", description="First item number", nullable=true),
 *     @OA\Property(property="to", type="integer", description="Last item number", nullable=true),
 *     @OA\Property(property="has_more_pages", type="boolean", description="Has more pages")
 * )
 * 
 * @OA\Schema(
 *     schema="ValidationError",
 *     @OA\Property(property="message", type="string", description="Error message"),
 *     @OA\Property(property="errors", type="object", description="Field validation errors",
 *         @OA\AdditionalProperties(
 *             type="array",
 *             @OA\Items(type="string")
 *         )
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="User",
 *     @OA\Property(property="id", type="integer", description="User ID"),
 *     @OA\Property(property="name", type="string", description="User name"),
 *     @OA\Property(property="email", type="string", description="User email (conditional)"),
 *     @OA\Property(property="avatar_url", type="string", description="User avatar URL", nullable=true),
 *     @OA\Property(property="role", type="string", description="User role", nullable=true),
 *     @OA\Property(property="is_active", type="boolean", description="User is active"),
 *     @OA\Property(property="last_seen_at", type="string", format="date-time", description="Last seen timestamp", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp")
 * )
 * 
 * @OA\Schema(
 *     schema="Movie",
 *     @OA\Property(property="id", type="integer", description="Movie ID"),
 *     @OA\Property(property="title", type="string", description="Movie title"),
 *     @OA\Property(property="original_title", type="string", description="Original movie title", nullable=true),
 *     @OA\Property(property="director", type="string", description="Movie director"),
 *     @OA\Property(property="year", type="integer", description="Release year"),
 *     @OA\Property(property="duration", type="integer", description="Duration in seconds", nullable=true),
 *     @OA\Property(property="genre", type="string", description="Movie genre"),
 *     @OA\Property(property="rating", type="string", description="Movie rating", nullable=true),
 *     @OA\Property(property="synopsis", type="string", description="Movie synopsis", nullable=true),
 *     @OA\Property(property="poster_url", type="string", description="Poster URL", nullable=true),
 *     @OA\Property(property="trailer_url", type="string", description="Trailer URL", nullable=true),
 *     @OA\Property(property="dcp_status", type="string", enum={"pending", "uploading", "processing", "completed", "failed"}, description="DCP processing status"),
 *     @OA\Property(property="dcp_size", type="integer", description="DCP file size in bytes", nullable=true),
 *     @OA\Property(property="dcp_checksum", type="string", description="DCP file checksum", nullable=true),
 *     @OA\Property(property="dcp_created_at", type="string", format="date-time", description="DCP creation timestamp", nullable=true),
 *     @OA\Property(property="dcp_validated_at", type="string", format="date-time", description="DCP validation timestamp", nullable=true),
 *     @OA\Property(property="processing_status", type="string", description="Current processing status"),
 *     @OA\Property(property="processing_progress", type="integer", description="Processing progress percentage"),
 *     @OA\Property(property="upload_status", type="string", description="Upload status"),
 *     @OA\Property(property="upload_progress", type="integer", description="Upload progress percentage"),
 *     @OA\Property(property="uploaded_at", type="string", format="date-time", description="Upload timestamp", nullable=true),
 *     @OA\Property(property="file_size_human", type="string", description="Human readable file size", nullable=true),
 *     @OA\Property(property="duration_human", type="string", description="Human readable duration", nullable=true),
 *     @OA\Property(property="validation_status", type="object", description="Validation status information",
 *         @OA\Property(property="is_valid", type="boolean"),
 *         @OA\Property(property="has_dcp", type="boolean"),
 *         @OA\Property(property="has_metadata", type="boolean"),
 *         @OA\Property(property="checksum_verified", type="boolean"),
 *         @OA\Property(property="last_validation", type="string", format="date-time", nullable=true)
 *     ),
 *     @OA\Property(property="can_download", type="boolean", description="User can download DCP"),
 *     @OA\Property(property="can_edit", type="boolean", description="User can edit movie"),
 *     @OA\Property(property="can_delete", type="boolean", description="User can delete movie"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Update timestamp"),
 *     @OA\Property(property="links", type="object", description="API resource links")
 * )
 * 
 * @OA\Schema(
 *     schema="Festival",
 *     @OA\Property(property="id", type="integer", description="Festival ID"),
 *     @OA\Property(property="name", type="string", description="Festival name"),
 *     @OA\Property(property="edition", type="string", description="Festival edition", nullable=true),
 *     @OA\Property(property="year", type="integer", description="Festival year"),
 *     @OA\Property(property="city", type="string", description="Festival city"),
 *     @OA\Property(property="country", type="string", description="Festival country"),
 *     @OA\Property(property="start_date", type="string", format="date", description="Festival start date", nullable=true),
 *     @OA\Property(property="end_date", type="string", format="date", description="Festival end date", nullable=true),
 *     @OA\Property(property="description", type="string", description="Festival description", nullable=true),
 *     @OA\Property(property="website", type="string", description="Festival website URL", nullable=true),
 *     @OA\Property(property="contact_email", type="string", description="Contact email"),
 *     @OA\Property(property="contact_phone", type="string", description="Contact phone", nullable=true),
 *     @OA\Property(property="logo_url", type="string", description="Festival logo URL", nullable=true),
 *     @OA\Property(property="banner_url", type="string", description="Festival banner URL", nullable=true),
 *     @OA\Property(property="status", type="string", enum={"draft", "open", "closed", "completed"}, description="Festival status"),
 *     @OA\Property(property="is_active", type="boolean", description="Festival is active"),
 *     @OA\Property(property="is_public", type="boolean", description="Festival is publicly visible"),
 *     @OA\Property(property="allows_submissions", type="boolean", description="Festival accepts submissions"),
 *     @OA\Property(property="submission_deadline", type="string", format="date", description="Submission deadline", nullable=true),
 *     @OA\Property(property="notification_date", type="string", format="date", description="Notification date", nullable=true),
 *     @OA\Property(property="movies_count", type="integer", description="Number of associated movies"),
 *     @OA\Property(property="completed_submissions", type="integer", description="Number of completed submissions"),
 *     @OA\Property(property="pending_submissions", type="integer", description="Number of pending submissions"),
 *     @OA\Property(property="failed_submissions", type="integer", description="Number of failed submissions"),
 *     @OA\Property(property="duration_days", type="integer", description="Festival duration in days", nullable=true),
 *     @OA\Property(property="is_upcoming", type="boolean", description="Festival is upcoming"),
 *     @OA\Property(property="is_ongoing", type="boolean", description="Festival is ongoing"),
 *     @OA\Property(property="is_past", type="boolean", description="Festival is past"),
 *     @OA\Property(property="submission_open", type="boolean", description="Submissions are open"),
 *     @OA\Property(property="days_until_start", type="integer", description="Days until festival starts", nullable=true),
 *     @OA\Property(property="days_until_deadline", type="integer", description="Days until submission deadline", nullable=true),
 *     @OA\Property(property="can_submit", type="boolean", description="User can submit to festival"),
 *     @OA\Property(property="can_edit", type="boolean", description="User can edit festival"),
 *     @OA\Property(property="can_delete", type="boolean", description="User can delete festival"),
 *     @OA\Property(property="can_manage_submissions", type="boolean", description="User can manage submissions"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Update timestamp"),
 *     @OA\Property(property="links", type="object", description="API resource links")
 * )
 * 
 * @OA\Schema(
 *     schema="Job",
 *     @OA\Property(property="id", type="integer", description="Job ID"),
 *     @OA\Property(property="job_id", type="string", description="Queue job ID", nullable=true),
 *     @OA\Property(property="job_class", type="string", description="Job class name"),
 *     @OA\Property(property="job_type", type="string", description="Human readable job type"),
 *     @OA\Property(property="status", type="string", enum={"pending", "processing", "completed", "failed", "cancelled"}, description="Job status"),
 *     @OA\Property(property="progress", type="integer", description="Job progress percentage"),
 *     @OA\Property(property="total_steps", type="integer", description="Total processing steps", nullable=true),
 *     @OA\Property(property="current_step", type="integer", description="Current processing step", nullable=true),
 *     @OA\Property(property="current_operation", type="string", description="Current operation description", nullable=true),
 *     @OA\Property(property="started_at", type="string", format="date-time", description="Job start timestamp", nullable=true),
 *     @OA\Property(property="completed_at", type="string", format="date-time", description="Job completion timestamp", nullable=true),
 *     @OA\Property(property="failed_at", type="string", format="date-time", description="Job failure timestamp", nullable=true),
 *     @OA\Property(property="estimated_completion", type="string", format="date-time", description="Estimated completion time", nullable=true),
 *     @OA\Property(property="duration", type="integer", description="Job duration in seconds", nullable=true),
 *     @OA\Property(property="duration_human", type="string", description="Human readable duration", nullable=true),
 *     @OA\Property(property="error_message", type="string", description="Error message if failed", nullable=true),
 *     @OA\Property(property="retry_count", type="integer", description="Number of retry attempts"),
 *     @OA\Property(property="max_retries", type="integer", description="Maximum retry attempts"),
 *     @OA\Property(property="progress_percentage", type="number", format="float", description="Detailed progress percentage"),
 *     @OA\Property(property="remaining_steps", type="integer", description="Remaining processing steps"),
 *     @OA\Property(property="queue", type="string", description="Queue name", nullable=true),
 *     @OA\Property(property="connection", type="string", description="Queue connection", nullable=true),
 *     @OA\Property(property="priority", type="integer", description="Job priority", nullable=true),
 *     @OA\Property(property="memory_usage", type="integer", description="Memory usage in bytes", nullable=true),
 *     @OA\Property(property="memory_usage_human", type="string", description="Human readable memory usage", nullable=true),
 *     @OA\Property(property="is_processing", type="boolean", description="Job is currently processing"),
 *     @OA\Property(property="is_completed", type="boolean", description="Job is completed"),
 *     @OA\Property(property="is_failed", type="boolean", description="Job has failed"),
 *     @OA\Property(property="is_cancelled", type="boolean", description="Job is cancelled"),
 *     @OA\Property(property="can_retry", type="boolean", description="Job can be retried"),
 *     @OA\Property(property="can_cancel", type="boolean", description="Job can be cancelled"),
 *     @OA\Property(property="throughput", type="number", format="float", description="Processing throughput (steps/second)", nullable=true),
 *     @OA\Property(property="average_step_duration", type="number", format="float", description="Average duration per step", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Update timestamp"),
 *     @OA\Property(property="links", type="object", description="API resource links")
 * )
 */
