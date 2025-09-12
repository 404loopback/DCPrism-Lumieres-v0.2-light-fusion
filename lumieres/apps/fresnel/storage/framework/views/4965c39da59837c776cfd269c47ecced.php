<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $__env->yieldContent('meta_description', 'DCPrism - Plateforme de gestion DCP pour festivals de cinéma. Upload, validation technique et distribution sécurisée de vos contenus cinématographiques.'); ?>">
    <meta name="keywords" content="DCP, Digital Cinema Package, festival, cinéma, upload, validation technique, distribution">
    <meta name="author" content="DCPrism">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo $__env->yieldContent('title', 'DCPrism - Plateforme DCP pour Festivals'); ?>">
    <meta property="og:description" content="<?php echo $__env->yieldContent('meta_description', 'Plateforme professionnelle de gestion DCP pour festivals de cinéma'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo e(url()->current()); ?>">
    
    <title><?php echo $__env->yieldContent('title', 'DCPrism - Plateforme DCP pour Festivals'); ?></title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">
    <link rel="icon" type="image/svg+xml" href="<?php echo e(asset('favicon.svg')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('images/logo-dcprism.png')); ?>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Vite Assets -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    
    <!-- Custom Showcase Styles -->
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #1d4ed8;
            --secondary: #10b981;
            --dark: #1f2937;
            --light: #f8fafc;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        }
        
        .text-gradient {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .animate-fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-bounce-slow {
            animation: bounce 3s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
    </style>
</head>
<body class="font-['Inter'] antialiased bg-white text-gray-900">
    <!-- Navigation -->
    <nav class="fixed w-full z-50 bg-white/80 backdrop-blur-md border-b border-gray-200/20 transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="<?php echo e(route('showcase.home')); ?>" class="flex items-center">
                        <img src="<?php echo e(asset('images/logo-dcprism.svg')); ?>" alt="DCPrism" class="w-10 h-10 mr-3">
                        <span class="text-xl font-bold text-gray-900">DCPrism</span>
                    </a>
                </div>

                <!-- Navigation Desktop -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-8">
                        <a href="<?php echo e(route('showcase.home')); ?>" 
                           class="nav-link <?php echo e(request()->routeIs('showcase.home') ? 'text-blue-600' : 'text-gray-700'); ?> hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors">
                            Accueil
                        </a>
                        <a href="<?php echo e(route('showcase.features')); ?>" 
                           class="nav-link <?php echo e(request()->routeIs('showcase.features') ? 'text-blue-600' : 'text-gray-700'); ?> hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors">
                            Fonctionnalités
                        </a>
                        <a href="<?php echo e(route('showcase.about')); ?>" 
                           class="nav-link <?php echo e(request()->routeIs('showcase.about') ? 'text-blue-600' : 'text-gray-700'); ?> hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors">
                            À Propos
                        </a>
                        <a href="<?php echo e(route('showcase.contact')); ?>" 
                           class="nav-link <?php echo e(request()->routeIs('showcase.contact') ? 'text-blue-600' : 'text-gray-700'); ?> hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors">
                            Contact
                        </a>
                    </div>
                </div>

                <!-- CTA Button -->
                <div class="hidden md:block">
                    <a href="/panel/login" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                        Accéder à la plateforme
                    </a>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button" class="mobile-menu-button p-2 rounded-md text-gray-700 hover:text-blue-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="md:hidden mobile-menu hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 bg-white border-t border-gray-200">
                <a href="<?php echo e(route('showcase.home')); ?>" 
                   class="block px-3 py-2 text-gray-700 hover:text-blue-600 <?php echo e(request()->routeIs('showcase.home') ? 'text-blue-600' : ''); ?>">
                    Accueil
                </a>
                <a href="<?php echo e(route('showcase.features')); ?>" 
                   class="block px-3 py-2 text-gray-700 hover:text-blue-600 <?php echo e(request()->routeIs('showcase.features') ? 'text-blue-600' : ''); ?>">
                    Fonctionnalités
                </a>
                <a href="<?php echo e(route('showcase.about')); ?>" 
                   class="block px-3 py-2 text-gray-700 hover:text-blue-600 <?php echo e(request()->routeIs('showcase.about') ? 'text-blue-600' : ''); ?>">
                    À Propos
                </a>
                <a href="<?php echo e(route('showcase.contact')); ?>" 
                   class="block px-3 py-2 text-gray-700 hover:text-blue-600 <?php echo e(request()->routeIs('showcase.contact') ? 'text-blue-600' : ''); ?>">
                    Contact
                </a>
                <a href="/panel/login" 
                   class="block px-3 py-2 bg-blue-600 text-white rounded-lg mx-3 mt-4 text-center">
                    Accéder à la plateforme
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Logo & Description -->
                <div class="col-span-2">
                    <div class="flex items-center mb-4">
                        <img src="<?php echo e(asset('images/logo-dcprism.svg')); ?>" alt="DCPrism" class="w-10 h-10 mr-3">
                        <span class="text-xl font-bold">DCPrism</span>
                    </div>
                    <p class="text-gray-400 mb-4 max-w-md">
                        Plateforme professionnelle de gestion DCP pour festivals de cinéma. 
                        Upload, validation technique et distribution sécurisée de vos contenus.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Navigation</h3>
                    <ul class="space-y-2">
                        <li><a href="<?php echo e(route('showcase.home')); ?>" class="text-gray-400 hover:text-white transition-colors">Accueil</a></li>
                        <li><a href="<?php echo e(route('showcase.features')); ?>" class="text-gray-400 hover:text-white transition-colors">Fonctionnalités</a></li>
                        <li><a href="<?php echo e(route('showcase.about')); ?>" class="text-gray-400 hover:text-white transition-colors">À Propos</a></li>
                        <li><a href="<?php echo e(route('showcase.contact')); ?>" class="text-gray-400 hover:text-white transition-colors">Contact</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li>info@dcprism.com</li>
                        <li>+33 1 23 45 67 89</li>
                        <li>Paris, France</li>
                        <li><a href="/panel/login" class="text-blue-400 hover:text-blue-300 transition-colors">Portail Admin</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo e(date('Y')); ?> DCPrism. Tous droits réservés. | Développé avec ❤️ pour l'industrie cinématographique</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-button').addEventListener('click', function() {
            document.querySelector('.mobile-menu').classList.toggle('hidden');
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Navbar background on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('bg-white/95');
                navbar.classList.remove('bg-white/80');
            } else {
                navbar.classList.add('bg-white/80');
                navbar.classList.remove('bg-white/95');
            }
        });

        // Animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in-up');
                }
            });
        }, observerOptions);

        // Observe all elements with fade-in class
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.fade-in').forEach(el => {
                observer.observe(el);
            });
        });
    </script>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /var/www/resources/views/layouts/showcase.blade.php ENDPATH**/ ?>