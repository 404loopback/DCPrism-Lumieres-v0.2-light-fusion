<?php

namespace App\Observers;

use App\Models\Dcp;
use App\Jobs\ProcessDcpUploadJob;
use App\Services\UnifiedNomenclatureService;
use Illuminate\Support\Facades\Log;

class DcpObserver
{
    /**
     * Handle the Dcp "created" event.
     */
    public function created(Dcp $dcp): void
    {
        Log::info('[DcpObserver] DCP created', [
            'dcp_id' => $dcp->id,
            'movie_id' => $dcp->movie_id
        ]);

        // Si un fichier temporaire est présent (field 'file_upload' depuis le form)
        if (request()->hasFile('file_upload')) {
            $uploadedFile = request()->file('file_upload');
            
            // Stocker temporairement le fichier
            $tempPath = $uploadedFile->store('dcps/temp', 'local');
            
            Log::info('[DcpObserver] File uploaded to temp storage, dispatching job', [
                'dcp_id' => $dcp->id,
                'temp_path' => $tempPath,
                'file_size' => $uploadedFile->getSize()
            ]);
            
            // Déclencher le job d'upload Backblaze
            ProcessDcpUploadJob::dispatch($dcp, $tempPath);
        }
    }

    /**
     * Handle the Dcp "updated" event.
     */
    public function updated(Dcp $dcp): void
    {
        // Si le DCP a été associé à un film ou une version, générer la nomenclature
        if ($dcp->wasChanged(['movie_id', 'version_id']) && $dcp->movie && $dcp->version) {
            $this->updateNomenclature($dcp);
        }

        // Log des changements importants de statut
        if ($dcp->wasChanged('status')) {
            Log::info('[DcpObserver] DCP status changed', [
                'dcp_id' => $dcp->id,
                'old_status' => $dcp->getOriginal('status'),
                'new_status' => $dcp->status
            ]);
        }
    }

    /**
     * Handle the Dcp "deleting" event.
     */
    public function deleting(Dcp $dcp): void
    {
        Log::info('[DcpObserver] DCP being deleted', [
            'dcp_id' => $dcp->id,
            'backblaze_file_id' => $dcp->backblaze_file_id
        ]);

        // Si le DCP a un fichier Backblaze, programmer sa suppression
        if ($dcp->backblaze_file_id && $dcp->movie) {
            try {
                $backblazeService = app(\App\Services\BackblazeService::class);
                $deleted = $backblazeService->delete($dcp->movie);
                
                if ($deleted) {
                    Log::info('[DcpObserver] Backblaze file deleted successfully', [
                        'dcp_id' => $dcp->id,
                        'backblaze_file_id' => $dcp->backblaze_file_id
                    ]);
                } else {
                    Log::warning('[DcpObserver] Failed to delete Backblaze file', [
                        'dcp_id' => $dcp->id,
                        'backblaze_file_id' => $dcp->backblaze_file_id
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('[DcpObserver] Error deleting Backblaze file', [
                    'dcp_id' => $dcp->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Mettre à jour la nomenclature d'une version associée
     */
    private function updateNomenclature(Dcp $dcp): void
    {
        if (!$dcp->version || !$dcp->movie) {
            return;
        }

        try {
            $nomenclatureService = app(UnifiedNomenclatureService::class);
            $movie = $dcp->movie;
            $version = $dcp->version;
            
            // Obtenir le festival associé au film
            $festival = $movie->festivals()->first();
            if (!$festival) {
                return;
            }

            // Générer la nomenclature
            $nomenclature = $nomenclatureService->generateMovieNomenclature($movie, $festival);
            
            // Mettre à jour la version
            $version->update([
                'generated_nomenclature' => $nomenclature
            ]);

            Log::info('[DcpObserver] Nomenclature updated for version', [
                'dcp_id' => $dcp->id,
                'version_id' => $version->id,
                'nomenclature' => $nomenclature
            ]);

        } catch (\Exception $e) {
            Log::error('[DcpObserver] Failed to update nomenclature', [
                'dcp_id' => $dcp->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
