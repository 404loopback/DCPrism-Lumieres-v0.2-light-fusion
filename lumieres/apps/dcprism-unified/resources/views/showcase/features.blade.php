@extends('layouts.showcase')

@section('title', 'Fonctionnalités - DCPrism')
@section('meta_description', 'Découvrez toutes les fonctionnalités de DCPrism : upload sécurisé, validation technique DCI, gestion multi-festivals, tableau de bord temps réel et bien plus.')

@section('content')
<!-- Hero Section -->
<section class="pt-24 pb-16 gradient-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <div class="fade-in">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">
                Fonctionnalités Avancées
            </h1>
            <p class="text-xl md:text-2xl max-w-3xl mx-auto text-blue-100">
                Tout ce dont vous avez besoin pour gérer vos DCP de façon professionnelle et sécurisée
            </p>
        </div>
    </div>
</section>

<!-- Features Grid -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Upload & Storage -->
        <div class="grid lg:grid-cols-2 gap-12 items-center mb-20">
            <div class="fade-in">
                <div class="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Upload & Stockage Sécurisé</h2>
                <p class="text-lg text-gray-600 mb-6">
                    Infrastructure robuste pour gérer des fichiers DCP de plusieurs dizaines de Go avec sécurité enterprise.
                </p>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Upload multipart avec reprise automatique</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Chiffrement AES-256 en transit et au repos</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Stockage distribué Backblaze B2</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Déduplication automatique des fichiers</span>
                    </div>
                </div>
            </div>
            <div class="fade-in">
                <div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-2xl p-8">
                    <div class="text-center mb-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Capacité de Stockage</h3>
                        <div class="text-3xl font-bold text-blue-600">Illimitée</div>
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Taille max par fichier</span>
                            <span class="font-semibold">100 GB</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Types supportés</span>
                            <span class="font-semibold">DCP, MXF, MP4</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Redondance</span>
                            <span class="font-semibold">99.999999999%</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Disponibilité</span>
                            <span class="font-semibold">99.9% SLA</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Technical Validation -->
        <div class="grid lg:grid-cols-2 gap-12 items-center mb-20">
            <div class="order-2 lg:order-1 fade-in">
                <div class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-2xl p-8">
                    <h3 class="text-xl font-semibold text-gray-900 mb-6 text-center">Validation DCI Complète</h3>
                    <div class="space-y-4">
                        <div class="bg-white rounded-lg p-4 flex items-center justify-between">
                            <span class="text-gray-700">Conformité DCI</span>
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        </div>
                        <div class="bg-white rounded-lg p-4 flex items-center justify-between">
                            <span class="text-gray-700">Métadonnées CPL</span>
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        </div>
                        <div class="bg-white rounded-lg p-4 flex items-center justify-between">
                            <span class="text-gray-700">Structure PKL</span>
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        </div>
                        <div class="bg-white rounded-lg p-4 flex items-center justify-between">
                            <span class="text-gray-700">Certificats KDM</span>
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        </div>
                        <div class="bg-white rounded-lg p-4 flex items-center justify-between">
                            <span class="text-gray-700">Format J2K</span>
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="order-1 lg:order-2 fade-in">
                <div class="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Validation Technique Automatisée</h2>
                <p class="text-lg text-gray-600 mb-6">
                    Analyse complète de la conformité DCP selon les standards DCI avec rapports détaillés et recommandations.
                </p>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Analyse des métadonnées CPL/PKL</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Vérification des checksums</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Test de compatibilité projecteurs</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Validation experte manuelle</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Multi-Festival Management -->
        <div class="grid lg:grid-cols-2 gap-12 items-center mb-20">
            <div class="fade-in">
                <div class="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Gestion Multi-Festivals</h2>
                <p class="text-lg text-gray-600 mb-6">
                    Interface unifiée pour gérer plusieurs festivals avec authentification par rôles et workflows personnalisés.
                </p>
                
                <div class="space-y-6">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Panels Dédiés</h4>
                        <p class="text-gray-600">Interfaces spécialisées pour Admin, Festivals et Techniciens avec permissions granulaires.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Nomenclature Personnalisée</h4>
                        <p class="text-gray-600">Système de nommage configurable par festival avec paramètres métier spécifiques.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Workflow Configurable</h4>
                        <p class="text-gray-600">Processus de validation adaptable selon les exigences de chaque festival.</p>
                    </div>
                </div>
            </div>
            <div class="fade-in">
                <div class="space-y-4">
                    <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6">
                        <div class="flex items-center mb-2">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                                <span class="text-white text-sm font-semibold">A</span>
                            </div>
                            <h4 class="font-semibold text-blue-900">Panel Admin</h4>
                        </div>
                        <p class="text-blue-700 text-sm">Gestion globale, configuration système, analytics avancés</p>
                    </div>
                    
                    <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-6">
                        <div class="flex items-center mb-2">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3">
                                <span class="text-white text-sm font-semibold">F</span>
                            </div>
                            <h4 class="font-semibold text-green-900">Panel Festival</h4>
                        </div>
                        <p class="text-green-700 text-sm">Gestion des soumissions, sélection films, programmation</p>
                    </div>
                    
                    <div class="bg-orange-50 border-l-4 border-orange-500 rounded-lg p-6">
                        <div class="flex items-center mb-2">
                            <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center mr-3">
                                <span class="text-white text-sm font-semibold">T</span>
                            </div>
                            <h4 class="font-semibold text-orange-900">Panel Technique</h4>
                        </div>
                        <p class="text-orange-700 text-sm">Validation DCP, contrôle qualité, rapports techniques</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monitoring & Analytics -->
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="order-2 lg:order-1 fade-in">
                <div class="bg-gradient-to-br from-purple-50 to-indigo-100 rounded-2xl p-8">
                    <h3 class="text-xl font-semibold text-gray-900 mb-6 text-center">Tableau de Bord Temps Réel</h3>
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="bg-white rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ $stats['total_movies'] ?? '0' }}</div>
                            <div class="text-sm text-gray-600">Films traités</div>
                        </div>
                        <div class="bg-white rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $stats['validated_movies'] ?? '0' }}</div>
                            <div class="text-sm text-gray-600">Validés</div>
                        </div>
                        <div class="bg-white rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-green-600">{{ $stats['total_festivals'] ?? '0' }}</div>
                            <div class="text-sm text-gray-600">Festivals actifs</div>
                        </div>
                        <div class="bg-white rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-orange-600">{{ $stats['processing_movies'] ?? '0' }}</div>
                            <div class="text-sm text-gray-600">En cours</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse mr-2"></div>
                        <span class="text-sm text-gray-600">Mise à jour en temps réel</span>
                    </div>
                </div>
            </div>
            <div class="order-1 lg:order-2 fade-in">
                <div class="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Monitoring & Analytics</h2>
                <p class="text-lg text-gray-600 mb-6">
                    Tableaux de bord interactifs, métriques en temps réel et rapports personnalisables pour un suivi optimal.
                </p>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Widgets interactifs personnalisables</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Alertes et notifications automatiques</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Rapports d'activité détaillés</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Export des données et audit trail</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Technical Specs -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 fade-in">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Spécifications Techniques</h2>
            <p class="text-xl text-gray-600">Architecture moderne et performante pour vos exigences professionnelles</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="fade-in card-hover bg-white rounded-lg p-6 text-center shadow-lg">
                <div class="text-3xl mb-4">⚡</div>
                <h3 class="text-lg font-semibold mb-2">Performance</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>Laravel 12 + PHP 8.3</li>
                    <li>Cache Redis</li>
                    <li>Queue asynchrone</li>
                    <li>CDN intégré</li>
                </ul>
            </div>

            <div class="fade-in card-hover bg-white rounded-lg p-6 text-center shadow-lg">
                <div class="text-3xl mb-4">🔒</div>
                <h3 class="text-lg font-semibold mb-2">Sécurité</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>Chiffrement AES-256</li>
                    <li>OAuth 2.0 + 2FA</li>
                    <li>Audit complet</li>
                    <li>GDPR compliant</li>
                </ul>
            </div>

            <div class="fade-in card-hover bg-white rounded-lg p-6 text-center shadow-lg">
                <div class="text-3xl mb-4">📊</div>
                <h3 class="text-lg font-semibold mb-2">Monitoring</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>Filament 4 dashboard</li>
                    <li>Laravel Telescope</li>
                    <li>Métriques temps réel</li>
                    <li>Logs centralisés</li>
                </ul>
            </div>

            <div class="fade-in card-hover bg-white rounded-lg p-6 text-center shadow-lg">
                <div class="text-3xl mb-4">☁️</div>
                <h3 class="text-lg font-semibold mb-2">Infrastructure</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>Cloud scalable</li>
                    <li>Backblaze B2</li>
                    <li>99.9% uptime</li>
                    <li>Auto-scaling</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-20 gradient-bg">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <div class="fade-in text-white">
            <h2 class="text-4xl font-bold mb-6">Prêt à tester ces fonctionnalités ?</h2>
            <p class="text-xl mb-8 text-blue-100">
                Découvrez par vous-même la puissance de DCPrism avec un accès complet à toutes les fonctionnalités.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-6 justify-center">
                <a href="/fresnel/login" 
                   class="bg-white text-blue-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-blue-50 transition-colors shadow-lg">
                    Commencer maintenant
                </a>
                <a href="{{ route('showcase.contact') }}" 
                   class="border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors">
                    Demander une démo
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
