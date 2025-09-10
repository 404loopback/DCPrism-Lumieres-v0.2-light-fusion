@extends('layouts.showcase')

@section('title', 'Contact - DCPrism')
@section('meta_description', 'Contactez l\'équipe DCPrism pour une démonstration, des questions techniques ou des informations sur nos solutions de gestion DCP pour festivals.')

@section('content')
<!-- Hero Section -->
<section class="pt-24 pb-16 gradient-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <div class="fade-in">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">
                Contactez-Nous
            </h1>
            <p class="text-xl md:text-2xl max-w-3xl mx-auto text-blue-100">
                Notre équipe est là pour répondre à vos questions et vous accompagner dans votre projet
            </p>
        </div>
    </div>
</section>

<!-- Contact Content -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12">
            
            <!-- Contact Form -->
            <div class="fade-in">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">Envoyez-nous un message</h2>
                
                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-green-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="text-green-800 font-medium">Message envoyé !</p>
                                <p class="text-green-600 text-sm mt-1">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-red-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="text-red-800 font-medium">Erreur</p>
                                <p class="text-red-600 text-sm mt-1">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
                
                <form action="{{ route('showcase.contact.submit') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nom complet *
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email professionnel *
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('email') border-red-500 @enderror">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                            Sujet *
                        </label>
                        <select id="subject" 
                                name="subject" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('subject') border-red-500 @enderror">
                            <option value="">Sélectionnez un sujet</option>
                            <option value="demo" {{ old('subject') == 'demo' ? 'selected' : '' }}>Demande de démonstration</option>
                            <option value="pricing" {{ old('subject') == 'pricing' ? 'selected' : '' }}>Informations tarifaires</option>
                            <option value="technical" {{ old('subject') == 'technical' ? 'selected' : '' }}>Question technique</option>
                            <option value="partnership" {{ old('subject') == 'partnership' ? 'selected' : '' }}>Partenariat</option>
                            <option value="support" {{ old('subject') == 'support' ? 'selected' : '' }}>Support utilisateur</option>
                            <option value="other" {{ old('subject') == 'other' ? 'selected' : '' }}>Autre</option>
                        </select>
                        @error('subject')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                            Message *
                        </label>
                        <textarea id="message" 
                                  name="message" 
                                  rows="6" 
                                  required
                                  placeholder="Décrivez votre projet, vos besoins ou vos questions..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="flex items-start">
                        <input type="checkbox" 
                               id="privacy" 
                               name="privacy" 
                               required
                               class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="privacy" class="ml-3 text-sm text-gray-600">
                            J'accepte que mes données soient utilisées pour répondre à ma demande. 
                            <a href="#" class="text-blue-600 hover:text-blue-500">Politique de confidentialité</a>
                        </label>
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-colors shadow-lg hover:shadow-xl">
                        Envoyer le message
                    </button>
                </form>
            </div>
            
            <!-- Contact Info -->
            <div class="fade-in">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">Informations de contact</h2>
                
                <div class="space-y-8">
                    <!-- Quick Contact -->
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-2xl p-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">Contact Direct</h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Email</p>
                                    <a href="mailto:contact@dcprism.com" class="text-blue-600 hover:text-blue-500">contact@dcprism.com</a>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Téléphone</p>
                                    <a href="tel:+33123456789" class="text-blue-600 hover:text-blue-500">+33 1 23 45 67 89</a>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Adresse</p>
                                    <p class="text-gray-600">Paris, France</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Support Options -->
                    <div class="space-y-6">
                        <h3 class="text-xl font-semibold text-gray-900">Options de Support</h3>
                        
                        <div class="grid gap-4">
                            <div class="border border-gray-200 rounded-lg p-6 hover:border-blue-300 transition-colors">
                                <h4 class="font-semibold text-gray-900 mb-2">🚀 Démonstration</h4>
                                <p class="text-gray-600 text-sm mb-3">
                                    Découvrez DCPrism en action avec une démo personnalisée de 30 minutes
                                </p>
                                <a href="mailto:demo@dcprism.com" class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                                    Programmer une démo →
                                </a>
                            </div>
                            
                            <div class="border border-gray-200 rounded-lg p-6 hover:border-green-300 transition-colors">
                                <h4 class="font-semibold text-gray-900 mb-2">🛠️ Support technique</h4>
                                <p class="text-gray-600 text-sm mb-3">
                                    Assistance experte pour l'intégration et l'utilisation de la plateforme
                                </p>
                                <a href="mailto:support@dcprism.com" class="text-green-600 hover:text-green-500 text-sm font-medium">
                                    Contacter le support →
                                </a>
                            </div>
                            
                            <div class="border border-gray-200 rounded-lg p-6 hover:border-purple-300 transition-colors">
                                <h4 class="font-semibold text-gray-900 mb-2">🤝 Partenariat</h4>
                                <p class="text-gray-600 text-sm mb-3">
                                    Explorez les opportunités de collaboration et de partenariat
                                </p>
                                <a href="mailto:partnership@dcprism.com" class="text-purple-600 hover:text-purple-500 text-sm font-medium">
                                    Discuter d'un partenariat →
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Response Time -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h4 class="font-semibold text-gray-900 mb-3">⚡ Temps de réponse</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Demande commerciale</span>
                                <span class="font-medium text-gray-900">< 2h</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Question technique</span>
                                <span class="font-medium text-gray-900">< 4h</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Support utilisateur</span>
                                <span class="font-medium text-gray-900">< 24h</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="fade-in text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-6">Questions Fréquentes</h2>
            <p class="text-xl text-gray-600">
                Retrouvez les réponses aux questions les plus courantes sur DCPrism
            </p>
        </div>
        
        <div class="space-y-6 fade-in">
            <div class="bg-white rounded-lg shadow-sm">
                <button class="faq-button w-full text-left p-6 focus:outline-none" type="button">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Quel est le coût de la plateforme DCPrism ?
                        </h3>
                        <svg class="faq-icon w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                    <div class="faq-content mt-4 text-gray-600 hidden">
                        DCPrism propose plusieurs formules adaptées à la taille de votre festival. Les tarifs incluent le stockage, la bande passante et le support technique. Contactez-nous pour un devis personnalisé basé sur vos besoins spécifiques.
                    </div>
                </button>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm">
                <button class="faq-button w-full text-left p-6 focus:outline-none" type="button">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Combien de temps faut-il pour mettre en place DCPrism ?
                        </h3>
                        <svg class="faq-icon w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                    <div class="faq-content mt-4 text-gray-600 hidden">
                        La mise en place est rapide : comptez 48h maximum après validation du contrat. Notre équipe configure votre environnement, importe vos données existantes et forme vos équipes. Vous pouvez commencer à utiliser la plateforme immédiatement.
                    </div>
                </button>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm">
                <button class="faq-button w-full text-left p-6 focus:outline-none" type="button">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Quels formats de fichiers DCP sont supportés ?
                        </h3>
                        <svg class="faq-icon w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                    <div class="faq-content mt-4 text-gray-600 hidden">
                        DCPrism supporte tous les formats DCP standards : 2K/4K, JPEG2000, PCM/Dolby, tous ratios d'aspect, et toutes versions linguistiques. La validation technique vérifie automatiquement la conformité aux standards DCI.
                    </div>
                </button>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm">
                <button class="faq-button w-full text-left p-6 focus:outline-none" type="button">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Comment est assurée la sécurité des contenus ?
                        </h3>
                        <svg class="faq-icon w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                    <div class="faq-content mt-4 text-gray-600 hidden">
                        Chiffrement AES-256 bout en bout, stockage sur infrastructure certifiée ISO 27001, accès contrôlé par rôles, audit trail complet, et sauvegardes automatiques multi-sites. Vos contenus sont protégés au niveau enterprise.
                    </div>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-20 gradient-bg">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <div class="fade-in text-white">
            <h2 class="text-4xl font-bold mb-6">Une question ? Parlons-en !</h2>
            <p class="text-xl mb-8 text-blue-100">
                Notre équipe d'experts est à votre disposition pour vous accompagner dans votre projet.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-6 justify-center">
                <a href="mailto:contact@dcprism.com" 
                   class="bg-white text-blue-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-blue-50 transition-colors shadow-lg">
                    Envoyer un email
                </a>
                <a href="tel:+33123456789" 
                   class="border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors">
                    Appeler maintenant
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // FAQ Toggle functionality
    document.querySelectorAll('.faq-button').forEach(button => {
        button.addEventListener('click', function() {
            const content = this.querySelector('.faq-content');
            const icon = this.querySelector('.faq-icon');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                content.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        });
    });
    
    // Auto-fill subject based on URL params
    const urlParams = new URLSearchParams(window.location.search);
    const subject = urlParams.get('subject');
    if (subject) {
        const subjectSelect = document.getElementById('subject');
        subjectSelect.value = subject;
    }
</script>
@endpush
