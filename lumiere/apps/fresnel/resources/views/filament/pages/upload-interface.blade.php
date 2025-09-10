<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Upload Form -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Upload DCP Files
            </h2>
            
            <form id="uploadForm" class="space-y-4">
                <div>
                    <label for="movieSelect" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Sélectionner un film
                    </label>
                    <select id="movieSelect" required
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">-- Choisir un film --</option>
                        @foreach($movies as $movie)
                            <option value="{{ $movie->id }}">{{ $movie->title }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="fileInput" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Fichier DCP
                    </label>
                    <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary-400 transition-colors cursor-pointer">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            <span class="font-semibold">Cliquez pour sélectionner</span> ou glissez-déposez votre fichier ici
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-500">
                            Formats supportés: DCP, ZIP, MP4, MOV (jusqu'à 50GB)
                        </p>
                        <input id="fileInput" type="file" class="sr-only" accept=".dcp,.zip,.mp4,.mov,.avi,.mkv">
                    </div>
                </div>
                
                <div id="fileInfo" class="hidden bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-blue-900 dark:text-blue-100" id="fileName"></p>
                            <p class="text-sm text-blue-700 dark:text-blue-200" id="fileSize"></p>
                        </div>
                        <button type="button" id="clearFile" class="text-blue-600 hover:text-blue-800">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <button type="submit" id="startUpload"
                        class="bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">
                        Démarrer l'upload
                    </button>
                    
                    <button type="button" id="cancelUpload" class="hidden bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg">
                        Annuler
                    </button>
                </div>
            </form>
        </div>

        <!-- Upload Progress -->
        <div id="uploadProgress" class="hidden bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Progression de l'upload</h3>
            
            <div class="space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300" id="progressText">0%</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400" id="speedText"></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                        <div id="progressBar" class="bg-primary-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Parties complétées:</span>
                        <span id="partsCompleted" class="font-medium text-gray-900 dark:text-white">0/0</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Données uploadées:</span>
                        <span id="bytesUploaded" class="font-medium text-gray-900 dark:text-white">0 MB</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Vitesse:</span>
                        <span id="uploadSpeed" class="font-medium text-gray-900 dark:text-white">-- MB/s</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Temps restant:</span>
                        <span id="timeRemaining" class="font-medium text-gray-900 dark:text-white">--</span>
                    </div>
                </div>
                
                <div id="uploadLogs" class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3 text-xs font-mono max-h-32 overflow-y-auto">
                    <div class="text-green-600">🚀 Upload initialisé...</div>
                </div>
            </div>
        </div>

        <!-- Resumable Uploads -->
        @if($resumableUploads->count() > 0)
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Uploads reprenables</h3>
            
            <div class="space-y-3">
                @foreach($resumableUploads as $upload)
                <div class="flex items-center justify-between p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ $upload->original_filename }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $upload->movie->title ?? 'Film inconnu' }} • 
                            {{ number_format($upload->progress_percentage, 1) }}% • 
                            {{ $upload->formatted_size }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-500">
                            Expire le {{ $upload->expires_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="resumeUpload({{ $upload->id }})" 
                            class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-1 px-3 rounded">
                            Reprendre
                        </button>
                        <button onclick="cancelUpload({{ $upload->id }})" 
                            class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-1 px-3 rounded">
                            Supprimer
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    @push('scripts')
    <script>
        // Configuration globale
        const CONFIG = {
            apiBaseUrl: '{{ $apiBaseUrl }}',
            csrfToken: '{{ $csrfToken }}',
            maxChunkSize: 500 * 1024 * 1024, // 500MB
            maxRetries: 3,
            retryDelay: 2000
        };

        // Variables globales
        let currentUpload = null;
        let selectedFile = null;
        let uploadAbortController = null;

        // Elements DOM
        const elements = {
            dropZone: document.getElementById('dropZone'),
            fileInput: document.getElementById('fileInput'),
            fileInfo: document.getElementById('fileInfo'),
            fileName: document.getElementById('fileName'),
            fileSize: document.getElementById('fileSize'),
            clearFile: document.getElementById('clearFile'),
            movieSelect: document.getElementById('movieSelect'),
            startUpload: document.getElementById('startUpload'),
            cancelUpload: document.getElementById('cancelUpload'),
            uploadProgress: document.getElementById('uploadProgress'),
            progressBar: document.getElementById('progressBar'),
            progressText: document.getElementById('progressText'),
            speedText: document.getElementById('speedText'),
            partsCompleted: document.getElementById('partsCompleted'),
            bytesUploaded: document.getElementById('bytesUploaded'),
            uploadSpeed: document.getElementById('uploadSpeed'),
            timeRemaining: document.getElementById('timeRemaining'),
            uploadLogs: document.getElementById('uploadLogs')
        };

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
        });

        function initializeEventListeners() {
            // Drag & Drop
            elements.dropZone.addEventListener('dragover', handleDragOver);
            elements.dropZone.addEventListener('drop', handleDrop);
            elements.dropZone.addEventListener('click', () => elements.fileInput.click());

            // File input
            elements.fileInput.addEventListener('change', handleFileSelect);
            elements.clearFile.addEventListener('click', clearSelectedFile);

            // Upload controls
            elements.startUpload.addEventListener('click', startUpload);
            elements.cancelUpload.addEventListener('click', cancelCurrentUpload);

            // Form submission
            document.getElementById('uploadForm').addEventListener('submit', function(e) {
                e.preventDefault();
                startUpload();
            });
        }

        function handleDragOver(e) {
            e.preventDefault();
            elements.dropZone.classList.add('border-primary-400');
        }

        function handleDrop(e) {
            e.preventDefault();
            elements.dropZone.classList.remove('border-primary-400');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                selectFile(files[0]);
            }
        }

        function handleFileSelect(e) {
            const files = e.target.files;
            if (files.length > 0) {
                selectFile(files[0]);
            }
        }

        function selectFile(file) {
            selectedFile = file;
            elements.fileName.textContent = file.name;
            elements.fileSize.textContent = formatFileSize(file.size);
            elements.fileInfo.classList.remove('hidden');
        }

        function clearSelectedFile() {
            selectedFile = null;
            elements.fileInput.value = '';
            elements.fileInfo.classList.add('hidden');
        }

        async function startUpload() {
            if (!selectedFile) {
                alert('Veuillez sélectionner un fichier');
                return;
            }

            if (!elements.movieSelect.value) {
                alert('Veuillez sélectionner un film');
                return;
            }

            try {
                elements.startUpload.disabled = true;
                elements.cancelUpload.classList.remove('hidden');
                elements.uploadProgress.classList.remove('hidden');

                uploadAbortController = new AbortController();

                // Étape 1: Générer le chemin d'upload
                addLog('📂 Génération du chemin d\'upload...');
                const pathResponse = await apiCall('/upload/generate-path', {
                    movie_id: parseInt(elements.movieSelect.value),
                    filename: selectedFile.name,
                    file_size: selectedFile.size,
                    mime_type: selectedFile.type || 'application/octet-stream'
                });

                // Étape 2: Initialiser le multipart upload
                addLog('🚀 Initialisation de l\'upload multipart...');
                const initResponse = await apiCall('/upload/initialize-multipart', {
                    movie_id: pathResponse.data.movie_id,
                    upload_path: pathResponse.data.upload_path,
                    filename: pathResponse.data.filename,
                    file_size: pathResponse.data.file_size,
                    mime_type: pathResponse.data.mime_type
                });

                currentUpload = {
                    id: initResponse.data.upload_id,
                    b2FileId: initResponse.data.b2_file_id,
                    totalParts: initResponse.data.total_parts,
                    chunkSize: initResponse.data.chunk_size,
                    completedParts: 0,
                    partSha1Array: [],
                    startTime: Date.now()
                };

                addLog(`✅ Upload initialisé: ${currentUpload.totalParts} parties de ${formatFileSize(currentUpload.chunkSize)}`);

                // Étape 3: Upload des chunks
                await uploadFileInChunks();

                // Étape 4: Finaliser l'upload
                addLog('🏁 Finalisation de l\'upload...');
                await apiCall('/upload/complete-multipart', {
                    upload_id: currentUpload.id,
                    part_sha1_array: currentUpload.partSha1Array
                });

                addLog('🎉 Upload complété avec succès !');
                setTimeout(() => {
                    location.reload();
                }, 2000);

            } catch (error) {
                console.error('Erreur upload:', error);
                addLog(`❌ Erreur: ${error.message}`, 'error');
                
                if (currentUpload) {
                    try {
                        await apiCall('/upload/abort-multipart', {
                            upload_id: currentUpload.id
                        });
                    } catch (abortError) {
                        console.error('Erreur annulation:', abortError);
                    }
                }
            } finally {
                elements.startUpload.disabled = false;
                elements.cancelUpload.classList.add('hidden');
                uploadAbortController = null;
                currentUpload = null;
            }
        }

        async function uploadFileInChunks() {
            const totalChunks = currentUpload.totalParts;
            const chunkSize = currentUpload.chunkSize;

            for (let i = 0; i < totalChunks; i++) {
                if (uploadAbortController?.signal.aborted) {
                    throw new Error('Upload annulé');
                }

                const start = i * chunkSize;
                const end = Math.min(start + chunkSize, selectedFile.size);
                const chunk = selectedFile.slice(start, end);
                const partNumber = i + 1;

                addLog(`📦 Upload partie ${partNumber}/${totalChunks} (${formatFileSize(chunk.size)})`);

                // Obtenir l'URL présignée
                const urlResponse = await apiCall('/upload/get-presigned-url', {
                    upload_id: currentUpload.id,
                    part_number: partNumber
                });

                // Calculer le SHA1 du chunk
                const sha1Hash = await calculateSHA1(chunk);

                // Uploader le chunk directement vers B2
                const uploadResponse = await uploadChunkToB2(
                    urlResponse.data.upload_url,
                    urlResponse.data.authorization_token,
                    chunk,
                    partNumber,
                    sha1Hash
                );

                // Sauvegarder le SHA1 pour la finalisation
                currentUpload.partSha1Array[i] = sha1Hash;
                currentUpload.completedParts++;

                // Mettre à jour la progression
                await updateProgress();
            }
        }

        async function uploadChunkToB2(uploadUrl, authToken, chunk, partNumber, sha1Hash) {
            const response = await fetch(uploadUrl, {
                method: 'POST',
                headers: {
                    'Authorization': authToken,
                    'X-Bz-Part-Number': partNumber.toString(),
                    'Content-Length': chunk.size.toString(),
                    'X-Bz-Content-Sha1': sha1Hash,
                    'Content-Type': 'application/octet-stream'
                },
                body: chunk,
                signal: uploadAbortController.signal
            });

            if (!response.ok) {
                throw new Error(`Erreur upload chunk ${partNumber}: ${response.status}`);
            }

            return response;
        }

        async function updateProgress() {
            const percentage = (currentUpload.completedParts / currentUpload.totalParts) * 100;
            const uploadedBytes = currentUpload.completedParts * currentUpload.chunkSize;
            const elapsedTime = (Date.now() - currentUpload.startTime) / 1000;
            const speed = uploadedBytes / elapsedTime / (1024 * 1024); // MB/s

            // Mise à jour UI
            elements.progressBar.style.width = `${percentage}%`;
            elements.progressText.textContent = `${percentage.toFixed(1)}%`;
            elements.partsCompleted.textContent = `${currentUpload.completedParts}/${currentUpload.totalParts}`;
            elements.bytesUploaded.textContent = formatFileSize(uploadedBytes);
            elements.uploadSpeed.textContent = `${speed.toFixed(1)} MB/s`;

            // Estimation temps restant
            const remainingBytes = selectedFile.size - uploadedBytes;
            const remainingTime = remainingBytes / (speed * 1024 * 1024);
            elements.timeRemaining.textContent = formatTime(remainingTime);

            // Mise à jour backend
            try {
                await apiCall('/upload/update-progress', {
                    upload_id: currentUpload.id,
                    completed_parts: currentUpload.completedParts,
                    uploaded_bytes: uploadedBytes,
                    upload_speed_mbps: speed
                });
            } catch (error) {
                console.warn('Erreur mise à jour progression:', error);
            }
        }

        async function cancelCurrentUpload() {
            if (!uploadAbortController) return;

            uploadAbortController.abort();
            
            if (currentUpload) {
                try {
                    await apiCall('/upload/abort-multipart', {
                        upload_id: currentUpload.id
                    });
                    addLog('🛑 Upload annulé');
                } catch (error) {
                    console.error('Erreur annulation:', error);
                }
            }
        }

        // Fonctions utilitaires
        async function apiCall(endpoint, data) {
            const response = await fetch(`${CONFIG.apiBaseUrl}${endpoint}`, {
                method: data ? 'POST' : 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CONFIG.csrfToken,
                    'Accept': 'application/json'
                },
                body: data ? JSON.stringify(data) : undefined,
                signal: uploadAbortController?.signal
            });

            if (!response.ok) {
                const error = await response.json().catch(() => ({ message: response.statusText }));
                throw new Error(error.message || `HTTP ${response.status}`);
            }

            return response.json();
        }

        async function calculateSHA1(chunk) {
            const buffer = await chunk.arrayBuffer();
            const hashBuffer = await crypto.subtle.digest('SHA-1', buffer);
            const hashArray = Array.from(new Uint8Array(hashBuffer));
            return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
        }

        function formatFileSize(bytes) {
            const units = ['B', 'KB', 'MB', 'GB', 'TB'];
            let size = bytes;
            let unitIndex = 0;

            while (size >= 1024 && unitIndex < units.length - 1) {
                size /= 1024;
                unitIndex++;
            }

            return `${size.toFixed(1)} ${units[unitIndex]}`;
        }

        function formatTime(seconds) {
            if (seconds < 60) return `${Math.round(seconds)}s`;
            if (seconds < 3600) return `${Math.round(seconds / 60)}min`;
            return `${(seconds / 3600).toFixed(1)}h`;
        }

        function addLog(message, type = 'info') {
            const logEntry = document.createElement('div');
            logEntry.className = type === 'error' ? 'text-red-600' : 'text-gray-600';
            logEntry.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
            elements.uploadLogs.appendChild(logEntry);
            elements.uploadLogs.scrollTop = elements.uploadLogs.scrollHeight;
        }

        // Fonctions pour uploads reprenables
        async function resumeUpload(uploadId) {
            try {
                addLog(`🔄 Reprise de l'upload ${uploadId}...`);
                alert('Fonctionnalité de reprise en cours de développement');
            } catch (error) {
                console.error('Erreur reprise upload:', error);
                alert('Erreur lors de la reprise: ' + error.message);
            }
        }

        async function cancelUpload(uploadId) {
            if (confirm('Êtes-vous sûr de vouloir annuler cet upload ?')) {
                try {
                    await apiCall('/upload/abort-multipart', { upload_id: uploadId });
                    location.reload();
                } catch (error) {
                    console.error('Erreur annulation upload:', error);
                    alert('Erreur lors de l\'annulation: ' + error.message);
                }
            }
        }
    </script>
    @endpush
</x-filament-panels::page>
