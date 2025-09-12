<?php $__env->startSection('title', 'DCPrism - Plateforme professionnelle de gestion DCP'); ?>
<?php $__env->startSection('meta_description', 'DCPrism facilite la gestion des Digital Cinema Package (DCP) pour les festivals de cinéma. Upload, validation technique automatisée, distribution sécurisée et tableau de bord temps réel.'); ?>

<?php $__env->startSection('content'); ?>
<!-- Hero Section -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <!-- Background gradient -->
    <div class="absolute inset-0 gradient-bg"></div>
    <div class="absolute inset-0 bg-black/20"></div>
    
    <!-- Content -->
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <div class="fade-in">
            <h1 class="text-5xl md:text-7xl font-bold mb-6 leading-tight">
                Gérez vos <span class="text-gradient">DCP</span><br>
                en toute simplicité
            </h1>
            <p class="text-xl md:text-2xl mb-12 max-w-3xl mx-auto text-blue-100">
                Plateforme professionnelle pour festivals de cinéma. Upload, validation technique et distribution de vos contenus Digital Cinema Package.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-6 justify-center">
                <a href="/panel/login" 
                   class="bg-white text-blue-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-blue-50 transition-colors shadow-lg">
                    Accéder à la plateforme
                </a>
                <a href="<?php echo e(route('showcase.features')); ?>" 
                   class="border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors">
                    Découvrir les fonctionnalités
                </a>
            </div>
        </div>

        <!-- Stats cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-20 fade-in">
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 text-center">
                <div class="text-3xl font-bold text-white" id="stat-movies"><?php echo e($stats['total_movies']); ?></div>
                <div class="text-blue-200 mt-2">Films traités</div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 text-center">
                <div class="text-3xl font-bold text-white" id="stat-festivals"><?php echo e($stats['total_festivals']); ?></div>
                <div class="text-blue-200 mt-2">Festivals actifs</div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 text-center">
                <div class="text-3xl font-bold text-white" id="stat-validated"><?php echo e($stats['validated_movies']); ?></div>
                <div class="text-blue-200 mt-2">Films validés</div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 text-center">
                <div class="text-3xl font-bold text-white" id="stat-storage"><?php echo e($stats['storage_used']); ?></div>
                <div class="text-blue-200 mt-2">Stockage utilisé</div>
            </div>
        </div>
    </div>

    <!-- Scroll indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2">
        <div class="animate-bounce-slow">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
        </div>
    </div>
</section>

<!-- Features Preview -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 fade-in">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Une solution complète pour vos DCP
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                De l'upload à la distribution, DCPrism automatise et sécurise chaque étape de votre workflow cinéma numérique.
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <div class="fade-in card-hover bg-white rounded-2xl p-8 shadow-lg">
                <div class="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Upload sécurisé</h3>
                <p class="text-gray-600 mb-6">
                    Upload de fichiers DCP volumineux avec reprise automatique, chiffrement de bout en bout et stockage cloud sécurisé.
                </p>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Reprise d'upload automatique
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Chiffrement AES-256
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Stockage Backblaze B2
                    </li>
                </ul>
            </div>

            <div class="fade-in card-hover bg-white rounded-2xl p-8 shadow-lg">
                <div class="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Validation technique</h3>
                <p class="text-gray-600 mb-6">
                    Analyse automatisée de la conformité DCP selon les standards DCI et validation manuelle par des experts techniques.
                </p>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Conformité DCI standard
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Analyse métadonnées
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Rapports détaillés
                    </li>
                </ul>
            </div>

            <div class="fade-in card-hover bg-white rounded-2xl p-8 shadow-lg">
                <div class="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Gestion multi-festivals</h3>
                <p class="text-gray-600 mb-6">
                    Tableau de bord unifié pour gérer plusieurs festivals simultanément avec authentification par rôles et nomenclatures personnalisées.
                </p>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Panels dédiés par rôle
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Nomenclature personnalisée
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Suivi temps réel
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Architecture Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="fade-in">
                <h2 class="text-4xl font-bold text-gray-900 mb-6">
                    Architecture moderne et scalable
                </h2>
                <p class="text-lg text-gray-600 mb-8">
                    Construit avec Laravel 12 et Filament 4, DCPrism offre une infrastructure robuste capable de gérer des téraoctets de données avec une performance optimale.
                </p>
                
                <div class="space-y-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Performance optimisée</h3>
                            <p class="text-gray-600">Jobs asynchrones, cache Redis et CDN pour une expérience utilisateur fluide même avec des fichiers de plusieurs Go.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Sécurité enterprise</h3>
                            <p class="text-gray-600">Authentification multi-facteurs, audit trail complet et chiffrement de bout en bout pour protéger vos contenus précieux.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Monitoring avancé</h3>
                            <p class="text-gray-600">Tableaux de bord temps réel, alertes automatiques et analytics détaillés pour piloter vos opérations.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="fade-in">
                <div class="relative">
                    <div class="bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl p-8 text-white">
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="bg-white/20 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold"><?php echo e($stats['processing_movies'] ?? 0); ?></div>
                                <div class="text-sm opacity-90">En traitement</div>
                            </div>
                            <div class="bg-white/20 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold"><?php echo e($stats['active_users'] ?? 0); ?></div>
                                <div class="text-sm opacity-90">Utilisateurs actifs</div>
                            </div>
                        </div>
                        
                        <h3 class="text-xl font-semibold mb-4">Plateforme en temps réel</h3>
                        <p class="text-blue-100 mb-6">
                            Suivez vos uploads, validations et distributions en direct avec notre tableau de bord interactif.
                        </p>
                        
                        <div class="flex items-center justify-between text-sm">
                            <span class="opacity-90">Mise à jour automatique</span>
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse mr-2"></div>
                                <span>En ligne</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 gradient-bg">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <div class="fade-in text-white">
            <h2 class="text-4xl md:text-5xl font-bold mb-6">
                Prêt à révolutionner votre workflow DCP ?
            </h2>
            <p class="text-xl mb-10 text-blue-100">
                Rejoignez les festivals qui font confiance à DCPrism pour sécuriser et optimiser leur gestion de contenus cinéma numérique.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-6 justify-center">
                <a href="/panel/login" 
                   class="bg-white text-blue-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-blue-50 transition-colors shadow-lg">
                    Commencer gratuitement
                </a>
                <a href="<?php echo e(route('showcase.contact')); ?>" 
                   class="border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors">
                    Demander une démo
                </a>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Auto-refresh stats every 30 seconds
    setInterval(function() {
        fetch('<?php echo e(route("showcase.api.stats")); ?>')
            .then(response => response.json())
            .then(data => {
                document.getElementById('stat-movies').textContent = data.total_movies;
                document.getElementById('stat-festivals').textContent = data.total_festivals;
                document.getElementById('stat-validated').textContent = data.validated_movies;
                document.getElementById('stat-storage').textContent = data.storage_used;
            })
            .catch(error => console.log('Stats update error:', error));
    }, 30000);
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.showcase', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/showcase/home.blade.php ENDPATH**/ ?>