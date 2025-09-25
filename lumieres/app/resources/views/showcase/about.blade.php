@extends('layouts.showcase')

@section('title', 'À Propos - DCPrism')
@section('meta_description', 'Découvrez l\'histoire de DCPrism, notre mission pour simplifier la gestion DCP pour les festivals de cinéma et notre engagement envers l\'industrie cinématographique.')

@section('content')
<!-- Hero Section -->
<section class="pt-24 pb-16 gradient-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <div class="fade-in">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">
                Notre Mission
            </h1>
            <p class="text-xl md:text-2xl max-w-3xl mx-auto text-blue-100">
                Révolutionner la gestion des contenus cinéma numérique pour les festivals du monde entier
            </p>
        </div>
    </div>
</section>

<!-- Story Section -->
<section class="py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="fade-in text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-6">L'Histoire de DCPrism</h2>
            <p class="text-xl text-gray-600">
                Née de l'expérience terrain dans l'industrie cinématographique et du besoin urgent de simplifier les workflows DCP.
            </p>
        </div>

        <div class="space-y-12">
            <div class="fade-in">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Le Défi</h3>
                <p class="text-lg text-gray-600 mb-6">
                    Les festivals de cinéma font face à des défis croissants dans la gestion des Digital Cinema Package (DCP). 
                    Files d'attente d'upload interminables, validations techniques manuelles coûteuses, risques de sécurité, 
                    et absence d'outils unifiés pour coordonner les équipes dispersées.
                </p>
                <p class="text-lg text-gray-600">
                    Cette complexité nuit à la créativité et détourne les organisateurs de leur mission première : 
                    présenter le meilleur du cinéma au public.
                </p>
            </div>

            <div class="fade-in">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Notre Réponse</h3>
                <p class="text-lg text-gray-600 mb-6">
                    DCPrism a été conçu par des professionnels de l'industrie cinématographique et des experts techniques 
                    qui comprennent intimement ces enjeux. Notre plateforme automatise les tâches répétitives, 
                    sécurise les contenus précieux et offre une visibilité temps réel sur toutes les opérations.
                </p>
                <p class="text-lg text-gray-600">
                    Résultat : les équipes peuvent se concentrer sur la programmation artistique et l'expérience spectateur, 
                    pendant que DCPrism gère la logistique technique en arrière-plan.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="fade-in text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-6">Nos Valeurs</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Les principes qui guident le développement de DCPrism et notre approche de l'industrie cinématographique.
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <div class="fade-in card-hover bg-white rounded-2xl p-8 text-center shadow-lg">
                <div class="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Passion du Cinéma</h3>
                <p class="text-gray-600">
                    Nous croyons que le cinéma enrichit l'humanité. Chaque fonctionnalité de DCPrism est pensée pour servir cette mission culturelle.
                </p>
            </div>

            <div class="fade-in card-hover bg-white rounded-2xl p-8 text-center shadow-lg">
                <div class="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Sécurité Absolue</h3>
                <p class="text-gray-600">
                    Les œuvres cinématographiques sont précieuses. Notre infrastructure garantit leur protection avec les plus hauts standards de sécurité.
                </p>
            </div>

            <div class="fade-in card-hover bg-white rounded-2xl p-8 text-center shadow-lg">
                <div class="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Innovation Continue</h3>
                <p class="text-gray-600">
                    L'industrie évolue rapidement. Nous investissons constamment dans les technologies émergentes pour rester à la pointe.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Technology Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="fade-in">
                <h2 class="text-4xl font-bold text-gray-900 mb-6">
                    Architecture Technique Moderne
                </h2>
                <p class="text-lg text-gray-600 mb-8">
                    DCPrism repose sur une stack technologique éprouvée et moderne, conçue pour la scalabilité, 
                    la performance et la maintenabilité.
                </p>
                
                <div class="space-y-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                            <span class="text-red-600 font-bold text-sm">L12</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Laravel 12</h3>
                            <p class="text-gray-600">Framework PHP moderne avec performance optimisée et sécurité renforcée.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                            <span class="text-purple-600 font-bold text-sm">F4</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Filament 4</h3>
                            <p class="text-gray-600">Interface d'administration moderne avec components avancés et UX optimisée.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7c0-2.21-3.582-4-8-4s-8 1.79-8 4z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Infrastructure Cloud</h3>
                            <p class="text-gray-600">Stockage Backblaze B2, cache Redis, CDN global pour performance optimale.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="fade-in">
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl p-8 text-white">
                    <h3 class="text-xl font-semibold mb-6">Métriques de Performance</h3>
                    
                    <div class="space-y-6">
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-300">Uptime</span>
                                <span class="font-semibold">99.9%</span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: 99.9%"></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-300">Performance</span>
                                <span class="font-semibold">< 200ms</span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: 95%"></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-300">Sécurité</span>
                                <span class="font-semibold">A+</span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-2">
                                <div class="bg-purple-500 h-2 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-300">Satisfaction</span>
                                <span class="font-semibold">4.9/5</span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-2">
                                <div class="bg-yellow-500 h-2 rounded-full" style="width: 98%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-6 border-t border-gray-700 text-center">
                        <p class="text-gray-300 text-sm">Certifié ISO 27001 | GDPR Compliant</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="fade-in text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-6">L'Équipe</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Une équipe passionnée alliant expertise technique et connaissance approfondie de l'industrie cinématographique.
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <div class="fade-in card-hover bg-white rounded-2xl p-8 text-center shadow-lg">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-white text-2xl font-bold">DC</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Équipe Développement</h3>
                <p class="text-gray-600 mb-4">Experts Laravel & Filament</p>
                <p class="text-sm text-gray-500">
                    Ingénieurs passionnés par les technologies web modernes et les défis de performance à grande échelle.
                </p>
            </div>

            <div class="fade-in card-hover bg-white rounded-2xl p-8 text-center shadow-lg">
                <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-white text-2xl font-bold">CI</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Experts Cinéma</h3>
                <p class="text-gray-600 mb-4">Professionnels de l'industrie</p>
                <p class="text-sm text-gray-500">
                    Directeurs techniques de festivals et spécialistes DCP avec 15+ ans d'expérience terrain.
                </p>
            </div>

            <div class="fade-in card-hover bg-white rounded-2xl p-8 text-center shadow-lg">
                <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-white text-2xl font-bold">UX</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Design & UX</h3>
                <p class="text-gray-600 mb-4">Expérience utilisateur</p>
                <p class="text-sm text-gray-500">
                    Designers spécialisés dans les interfaces métier complexes et l'optimisation des workflows.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Commitment Section -->
<section class="py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="fade-in">
            <h2 class="text-4xl font-bold text-gray-900 mb-6">Notre Engagement</h2>
            <p class="text-xl text-gray-600 mb-8">
                DCPrism s'engage à accompagner l'évolution de l'industrie cinématographique avec 
                des solutions technologiques innovantes et une approche centrée sur l'utilisateur.
            </p>
            
            <div class="grid md:grid-cols-2 gap-8 text-left">
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">🎯 Innovation Continue</h3>
                    <p class="text-gray-600">
                        Développement actif avec nouvelles fonctionnalités basées sur les retours terrain 
                        et l'évolution des standards DCI.
                    </p>
                </div>
                
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">🤝 Support Expert</h3>
                    <p class="text-gray-600">
                        Équipe support technique disponible pour accompagner les festivals dans leur 
                        transition vers le numérique.
                    </p>
                </div>
                
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">🌍 Vision Globale</h3>
                    <p class="text-gray-600">
                        Plateforme conçue pour s'adapter aux spécificités culturelles et techniques 
                        des festivals du monde entier.
                    </p>
                </div>
                
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">🔒 Confiance Totale</h3>
                    <p class="text-gray-600">
                        Sécurité enterprise, conformité réglementaire et transparence dans toutes 
                        nos opérations.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-20 gradient-bg">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <div class="fade-in text-white">
            <h2 class="text-4xl font-bold mb-6">Rejoignez l'Aventure</h2>
            <p class="text-xl mb-8 text-blue-100">
                Participez à la révolution numérique du cinéma avec une plateforme conçue par et pour les professionnels.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-6 justify-center">
                <a href="/fresnel/login" 
                   class="bg-white text-blue-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-blue-50 transition-colors shadow-lg">
                    Découvrir la plateforme
                </a>
                <a href="{{ route('showcase.contact') }}" 
                   class="border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors">
                    Nous contacter
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
