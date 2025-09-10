<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DCPrism - Connexion</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-dcprism.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        body {
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }
        
        /* Fond noir texturé */
        .animated-gradient-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #0a0a0a;
            z-index: -4;
        }
        
        /* Spots de lumière RGB qui se croisent */
        .rgb-light-spot {
            position: fixed;
            width: 2200px;
            height: 2200px;
            border-radius: 50%;
            filter: blur(280px);
            opacity: 1.0;
            mix-blend-mode: screen;
        }
        
        .red-light {
            background: radial-gradient(circle, rgba(255, 50, 50, 1.0) 0%, rgba(255, 0, 0, 0.8) 40%, transparent 70%);
            top: -30%;
            left: -30%;
            animation: floatRed 25s ease-in-out infinite;
            z-index: -3;
        }
        
        .green-light {
            background: radial-gradient(circle, rgba(50, 255, 50, 1.0) 0%, rgba(0, 255, 0, 0.8) 40%, transparent 70%);
            top: -30%;
            right: -30%;
            animation: floatGreen 30s ease-in-out infinite;
            z-index: -3;
        }
        
        .blue-light {
            background: radial-gradient(circle, rgba(50, 50, 255, 1.0) 0%, rgba(0, 0, 255, 0.8) 40%, transparent 70%);
            bottom: -30%;
            left: 10%;
            animation: floatBlue 22s ease-in-out infinite;
            z-index: -3;
        }
        
        /* Texture subtile sur le fond */
        .texture-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.02) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.01) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.015) 0%, transparent 50%);
            z-index: -2;
        }
        
        
        
        /* Animations des spots de lumière RGB */
        @keyframes floatRed {
            0%, 100% {
                transform: translate(0px, 0px) scale(1);
            }
            25% {
                transform: translate(150px, -100px) scale(1.2);
            }
            50% {
                transform: translate(300px, 50px) scale(0.8);
            }
            75% {
                transform: translate(100px, 150px) scale(1.1);
            }
        }
        
        @keyframes floatGreen {
            0%, 100% {
                transform: translate(0px, 0px) scale(1);
            }
            33% {
                transform: translate(-200px, -150px) scale(1.3);
            }
            66% {
                transform: translate(-50px, 100px) scale(0.9);
            }
        }
        
        @keyframes floatBlue {
            0%, 100% {
                transform: translate(0px, 0px) scale(1);
            }
            30% {
                transform: translate(200px, -200px) scale(1.1);
            }
            60% {
                transform: translate(-150px, -50px) scale(1.4);
            }
            90% {
                transform: translate(50px, 100px) scale(0.7);
            }
        }
        
        /* Style glassmorphism de la boîte de connexion SANS animations */
        .login-container {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            /* PAS d'animation ici */
        }
        
        .logo-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px 20px 0 0;
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }
        
        /* Ajustement des couleurs pour glassmorphism */
        .logo-container h1 {
            color: rgba(60, 60, 60, 0.9) !important;
        }
        
        .logo-container p {
            color: rgba(80, 80, 80, 0.8) !important;
        }
        
        /* Styles pour les champs de formulaire sur glassmorphism */
        .login-container label {
            color: rgba(60, 60, 60, 0.9) !important;
        }
        
        .login-container input {
            background: rgba(255, 255, 255, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.4);
            color: rgba(60, 60, 60, 0.9);
        }
        
        .login-container input::placeholder {
            color: rgba(100, 100, 100, 0.7);
        }
        
        .login-container input:focus {
            background: rgba(255, 255, 255, 0.4);
            border-color: rgba(58, 134, 255, 0.7);
            box-shadow: 0 0 0 3px rgba(58, 134, 255, 0.2);
        }
        
        .login-container .text-gray-700 {
            color: rgba(60, 60, 60, 0.9) !important;
        }
        
        .bg-gray-50 {
            background: rgba(255, 255, 255, 0.2) !important;
        }
        
        .text-gray-600 {
            color: rgba(80, 80, 80, 0.8) !important;
        }
        
        .text-gray-400 {
            color: rgba(120, 120, 120, 0.7) !important;
        }
    </style>
</head>
<body class="antialiased">
    <!-- Fond noir avec spots RGB qui se croisent -->
    <div class="animated-gradient-bg"></div>
    <div class="texture-overlay"></div>
    
    <!-- Spots de lumière RGB -->
    <div class="rgb-light-spot red-light"></div>
    <div class="rgb-light-spot green-light"></div>
    <div class="rgb-light-spot blue-light"></div>
    
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="login-container w-full max-w-md">
            <!-- Header avec logo -->
            <div class="logo-container p-8 text-center border-b">
                <img src="{{ asset('images/logo-dcprism.svg') }}" alt="DCPrism" class="mx-auto h-12 mb-4">
                <h1 class="text-2xl font-bold text-gray-800">DCPrism</h1>
                <p class="text-gray-600 mt-2">Connexion à votre espace</p>
            </div>

            <!-- Formulaire de connexion -->
            <div class="p-8">
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="text-red-800 text-sm">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('panel.login') }}" class="space-y-6">
                    @csrf
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Adresse email
                        </label>
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            required 
                            autofocus
                            value="{{ old('email') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                            placeholder="votre@email.com"
                        >
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Mot de passe
                        </label>
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                            placeholder="Votre mot de passe"
                        >
                    </div>

                    <!-- Remember me -->
                    <div class="flex items-center">
                        <input 
                            id="remember" 
                            name="remember" 
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Se souvenir de moi
                        </label>
                    </div>

                    <!-- Submit button -->
                    <button 
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        Se connecter
                    </button>
                </form>

                <!-- Comptes de test avec boutons -->
                <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600 mb-3"><strong>Comptes de test :</strong></p>
                    <div class="grid grid-cols-2 gap-2">
                        <button 
                            type="button" 
                            onclick="fillCredentials('admin@dcprism.local', 'admin123')"
                            class="test-account-btn bg-red-500 hover:bg-red-600 text-white text-xs py-2 px-3 rounded transition duration-200"
                            title="Administrateur - Accès complet"
                        >
                            👑 Admin
                        </button>
                        
                        <button 
                            type="button" 
                            onclick="fillCredentials('tech@dcprism.local', 'password')"
                            class="test-account-btn bg-blue-500 hover:bg-blue-600 text-white text-xs py-2 px-3 rounded transition duration-200"
                            title="Technicien - Validation technique"
                        >
                            🔧 Tech
                        </button>
                        
                        <button 
                            type="button" 
                            onclick="fillCredentials('manager@dcprism.local', 'password')"
                            class="test-account-btn bg-green-500 hover:bg-green-600 text-white text-xs py-2 px-3 rounded transition duration-200"
                            title="Manager - Gestion des festivals"
                        >
                            👔 Manager
                        </button>
                        
                        <button 
                            type="button" 
                            onclick="fillCredentials('supervisor@dcprism.local', 'password')"
                            class="test-account-btn bg-purple-500 hover:bg-purple-600 text-white text-xs py-2 px-3 rounded transition duration-200"
                            title="Superviseur - Supervision globale"
                        >
                            👥 Supervisor
                        </button>
                        
                        <button 
                            type="button" 
                            onclick="fillCredentials('source@dcprism.local', 'password')"
                            class="test-account-btn bg-orange-500 hover:bg-orange-600 text-white text-xs py-2 px-3 rounded transition duration-200"
                            title="Source - Soumission de films"
                        >
                            🎬 Source
                        </button>
                        
                        <button 
                            type="button" 
                            onclick="fillCredentials('cinema@dcprism.local', 'password')"
                            class="test-account-btn bg-indigo-500 hover:bg-indigo-600 text-white text-xs py-2 px-3 rounded transition duration-200"
                            title="Cinéma - Accès aux DCP"
                        >
                            🎭 Cinema
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-3 text-center">
                        Cliquez sur un bouton pour remplir automatiquement les champs
                    </p>
                </div>
            </div>
        </div>
    </div>
    <script>
        function fillCredentials(email, password) {
            // Remplir les champs
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
            
            // Ajouter un effet visuel
            const emailField = document.getElementById('email');
            const passwordField = document.getElementById('password');
            
            // Animation de highlight
            emailField.classList.add('ring-2', 'ring-green-400');
            passwordField.classList.add('ring-2', 'ring-green-400');
            
            // Retirer l'effet après 1 seconde
            setTimeout(() => {
                emailField.classList.remove('ring-2', 'ring-green-400');
                passwordField.classList.remove('ring-2', 'ring-green-400');
            }, 1000);
            
            // Focus sur le bouton de connexion pour une UX fluide
            document.querySelector('button[type="submit"]').focus();
        }
        
        // Optionnel : permettre la connexion directe avec Ctrl+Click
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('test-account-btn') && e.ctrlKey) {
                e.preventDefault();
                // Remplir automatiquement et soumettre
                const onclick = e.target.getAttribute('onclick');
                const matches = onclick.match(/fillCredentials\('(.+)', '(.+)'\)/);
                if (matches) {
                    document.getElementById('email').value = matches[1];
                    document.getElementById('password').value = matches[2];
                    document.querySelector('form').submit();
                }
            }
        });
    </script>
</body>
</html>
