@vite(['resources/js/app.js', 'resources/css/app.css'])
<div class="login-container">
    <style>
        body {
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }
        
        .login-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: #0a0a0a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Figtree', sans-serif;
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
        }
        
        .green-light {
            background: radial-gradient(circle, rgba(50, 255, 50, 1.0) 0%, rgba(0, 255, 0, 0.8) 40%, transparent 70%);
            top: -20%;
            right: -40%;
            animation: floatGreen 30s ease-in-out infinite reverse;
        }
        
        .blue-light {
            background: radial-gradient(circle, rgba(50, 50, 255, 1.0) 0%, rgba(0, 0, 255, 0.8) 40%, transparent 70%);
            bottom: -30%;
            left: -20%;
            animation: floatBlue 35s ease-in-out infinite;
        }
        
        @keyframes floatRed {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(200px, 100px) rotate(90deg); }
            50% { transform: translate(100px, -150px) rotate(180deg); }
            75% { transform: translate(-100px, 50px) rotate(270deg); }
        }
        
        @keyframes floatGreen {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(-150px, 200px) rotate(90deg); }
            50% { transform: translate(200px, 100px) rotate(180deg); }
            75% { transform: translate(50px, -100px) rotate(270deg); }
        }
        
        @keyframes floatBlue {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(100px, -200px) rotate(90deg); }
            50% { transform: translate(-200px, -50px) rotate(180deg); }
            75% { transform: translate(150px, 100px) rotate(270deg); }
        }
        
        /* Conteneur de connexion glassmorphism */
        .glass-login-card {
            position: relative;
            width: 400px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.3);
            z-index: 10;
        }
        
        .glass-login-card h1 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 600;
        }
        
        /* Styles pour les champs Filament */
        .glass-login-card .fi-form {
            color: white;
        }
        
        .glass-login-card .fi-input {
            background: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: white !important;
            backdrop-filter: blur(10px);
        }
        
        .glass-login-card .fi-input:focus {
            border-color: rgba(255, 255, 255, 0.4) !important;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.1) !important;
        }
        
        .glass-login-card .fi-btn-primary {
            background: rgba(255, 255, 255, 0.15) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .glass-login-card .fi-btn-primary:hover {
            background: rgba(255, 255, 255, 0.25) !important;
            border-color: rgba(255, 255, 255, 0.4) !important;
            transform: translateY(-2px);
        }
        
        .glass-login-card label {
            color: rgba(255, 255, 255, 0.9) !important;
        }
        
        /* Bouton flottant de navigation vers Meniscus - magnetic button */
        .floating-nav-arrow {
            position: fixed;
            top: 50%;
            right: 80px;
            /* Removed transform to avoid conflict with GSAP */
            margin-top: -35px; /* Half of height to center vertically */
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            z-index: 1000;
            cursor: pointer;
            outline: none;
        }
        
        .floating-nav-arrow:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
            color: white;
            text-decoration: none;
        }
        
        /* Magnetic button specific styles */
        .magnetic-button {
            /* Remove transform transition to let GSAP handle it completely */
        }
        
        .magnetic-button span {
            position: relative;
            display: inline-block;
            /* Remove transform transition to let GSAP handle it completely */
        }
        
        .arrow-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 24px;
            height: 24px;
            pointer-events: none;
        }
        
        .floating-nav-arrow svg {
            width: 100%;
            height: 100%;
        }
        
        /* Texte stationnaire Meniscus - ne bouge jamais */
        .meniscus-label {
            position: fixed;
            top: 50%;
            right: 115px; /* Button right (80px) + button width/2 (35px) */
            margin-top: -80px; /* Button height/2 + 45px spacing above */
            transform: translateX(50%); /* Center horizontally above button */
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            font-weight: 700;
            transition: opacity 0.3s ease-out;
            white-space: nowrap;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
            pointer-events: none;
            z-index: 999; /* Below button but visible */
            text-align: center;
        }
        
        /* Styles needy pour l'effet magnétique - plus visible */
        .needy-btn.needy-attracted {
            background: rgba(255, 255, 255, 0.4) !important;
            border-color: rgba(255, 255, 255, 0.8) !important;
            box-shadow: 
                0 0 40px rgba(255, 255, 255, 0.5) !important,
                0 0 80px rgba(255, 255, 255, 0.25) !important,
                0 0 120px rgba(255, 255, 255, 0.15) !important,
                inset 0 0 30px rgba(255, 255, 255, 0.15) !important;
            /* Disable original animations when needy is active */
            animation: none !important;
        }
        
        /* Override hover styles when needy is active */
        .needy-btn.needy-attracted:hover {
            transform: none !important;
        }
        
        .needy-btn.needy-attracted .needy-indicator {
            opacity: 0;
            transform: translateX(-50%) scale(0.8);
        }
        
        
        /* Styles pour le logo */
        .logo-container {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .logo-container h1 {
            color: rgba(255, 255, 255, 0.95) !important;
            margin: 0 0 8px 0;
        }
        
        .logo-container p {
            color: rgba(255, 255, 255, 0.7) !important;
            margin: 0;
        }
        
        /* Styles pour les comptes de test */
        .test-accounts-container {
            margin-top: 30px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .test-accounts-title {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .test-accounts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 15px;
        }
        
        .test-account-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .admin-btn {
            background: rgba(239, 68, 68, 0.7);
        }
        
        .admin-btn:hover {
            background: rgba(239, 68, 68, 0.9);
            transform: translateY(-2px);
        }
        
        .tech-btn {
            background: rgba(59, 130, 246, 0.7);
        }
        
        .tech-btn:hover {
            background: rgba(59, 130, 246, 0.9);
            transform: translateY(-2px);
        }
        
        .manager-btn {
            background: rgba(34, 197, 94, 0.7);
        }
        
        .manager-btn:hover {
            background: rgba(34, 197, 94, 0.9);
            transform: translateY(-2px);
        }
        
        .supervisor-btn {
            background: rgba(168, 85, 247, 0.7);
        }
        
        .supervisor-btn:hover {
            background: rgba(168, 85, 247, 0.9);
            transform: translateY(-2px);
        }
        
        .source-btn {
            background: rgba(249, 115, 22, 0.7);
        }
        
        .source-btn:hover {
            background: rgba(249, 115, 22, 0.9);
            transform: translateY(-2px);
        }
        
        .cinema-btn {
            background: rgba(99, 102, 241, 0.7);
        }
        
        .cinema-btn:hover {
            background: rgba(99, 102, 241, 0.9);
            transform: translateY(-2px);
        }
        
        .test-accounts-help {
            color: rgba(255, 255, 255, 0.6);
            font-size: 11px;
            text-align: center;
            margin: 0;
        }
    </style>
    
    <!-- Spots de lumière RGB animés -->
    <div class="rgb-light-spot red-light"></div>
    <div class="rgb-light-spot green-light"></div>
    <div class="rgb-light-spot blue-light"></div>
    
    <!-- Carte de connexion glassmorphism -->
    <div class="glass-login-card">
        <!-- Header avec logo -->
        <div class="logo-container">
            <img src="{{ asset('images/logo-dcprism.svg') }}" alt="DCPrism" class="mx-auto h-12 mb-4">
            <h1 class="text-2xl font-bold mb-2">DCPrism</h1>
            <p class="text-sm mb-6">Connexion à votre espace</p>
        </div>
        
        <form wire:submit="authenticate">
            {{ $this->form }}
            
            <div style="margin-top: 20px;">
                <x-filament::button type="submit" size="lg" class="w-full">
                    Se connecter
                </x-filament::button>
            </div>
        </form>
        
        <!-- Comptes de test avec boutons -->
        <div class="test-accounts-container">
            <p class="test-accounts-title"><strong>Comptes de test :</strong></p>
            <div class="test-accounts-grid">
                <button 
                    type="button" 
                    onclick="fillCredentials('admin@dcprism.local', 'admin123')"
                    class="test-account-btn admin-btn"
                    title="Administrateur - Accès complet"
                >
                    👑 Admin
                </button>
                
                <button 
                    type="button" 
                    onclick="fillCredentials('tech@dcprism.local', 'password')"
                    class="test-account-btn tech-btn"
                    title="Technicien - Validation technique"
                >
                    🔧 Tech
                </button>
                
                <button 
                    type="button" 
                    onclick="fillCredentials('manager@dcprism.local', 'password')"
                    class="test-account-btn manager-btn"
                    title="Manager - Gestion des festivals"
                >
                    👔 Manager
                </button>
                
                <button 
                    type="button" 
                    onclick="fillCredentials('supervisor@dcprism.local', 'password')"
                    class="test-account-btn supervisor-btn"
                    title="Superviseur - Supervision globale"
                >
                    👥 Supervisor
                </button>
                
                <button 
                    type="button" 
                    onclick="fillCredentials('source@dcprism.local', 'password')"
                    class="test-account-btn source-btn"
                    title="Source - Soumission de films"
                >
                    🎬 Source
                </button>
                
                <button 
                    type="button" 
                    onclick="fillCredentials('cinema@dcprism.local', 'password')"
                    class="test-account-btn cinema-btn"
                    title="Cinéma - Accès aux DCP"
                >
                    🎭 Cinema
                </button>
            </div>
            <p class="test-accounts-help">
                Cliquez sur un bouton pour remplir automatiquement les champs
            </p>
        </div>
    </div>
    
    <!-- Texte stationnaire pour Meniscus -->
    <div class="meniscus-label">
        Meniscus
    </div>
    
    <!-- Bouton flottant vers Meniscus avec effet magnétique -->
    <a href="http://meniscus.localhost/panel/admin/login" class="magnetic-button floating-nav-arrow" title="Aller sur Meniscus">
        <div class="arrow-container">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
            </svg>
        </div>
    </a>
    
    <!-- GSAP CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    
    <script>
        function fillCredentials(email, password) {
            // Détecter les champs Filament dans le DOM
            const emailInput = document.querySelector('input[name="data.email"]') || document.querySelector('input[type="email"]');
            const passwordInput = document.querySelector('input[name="data.password"]') || document.querySelector('input[type="password"]');
            
            if (emailInput && passwordInput) {
                // Remplir les champs
                emailInput.value = email;
                passwordInput.value = password;
                
                // Declencher les événements pour notifier Livewire
                emailInput.dispatchEvent(new Event('input', { bubbles: true }));
                passwordInput.dispatchEvent(new Event('input', { bubbles: true }));
                emailInput.dispatchEvent(new Event('change', { bubbles: true }));
                passwordInput.dispatchEvent(new Event('change', { bubbles: true }));
                
                // Ajouter un effet visuel
                emailInput.classList.add('ring-2', 'ring-green-400');
                passwordInput.classList.add('ring-2', 'ring-green-400');
                
                // Retirer l'effet après 1 seconde
                setTimeout(() => {
                    emailInput.classList.remove('ring-2', 'ring-green-400');
                    passwordInput.classList.remove('ring-2', 'ring-green-400');
                }, 1000);
                
                // Focus sur le bouton de connexion pour une UX fluide
                const submitButton = document.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.focus();
                }
            } else {
                console.log('Champs de connexion non trouvés dans le DOM');
            }
        }
        
        // Optionnel : permettre la connexion directe avec Ctrl+Click
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('test-account-btn') && e.ctrlKey) {
                e.preventDefault();
                // Remplir automatiquement et soumettre
                const onclick = e.target.getAttribute('onclick');
                const matches = onclick.match(/fillCredentials\('(.+)', '(.+)'\)/);
                if (matches) {
                    fillCredentials(matches[1], matches[2]);
                    
                    // Attendre un peu puis soumettre le formulaire
                    setTimeout(() => {
                        const form = document.querySelector('form');
                        if (form) {
                            form.dispatchEvent(new Event('submit', { bubbles: true }));
                        }
                    }, 100);
                }
            }
        });
        
        // Initialize magnetic button behavior when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== MAGNETIC BUTTON DEBUG START ===');
            console.log('DOM loaded, checking GSAP...', typeof gsap);
            console.log('Checking initMagneticButton...', typeof window.initMagneticButton);
            console.log('Magnetic elements found:', document.querySelectorAll('.magnetic-button').length);
            console.log('Button element:', document.querySelector('.magnetic-button'));
            
            if (typeof window.initMagneticButton === 'function') {
                console.log('Initializing magnetic button behavior...');
                
                try {
                    window.initMagneticButton();
                    console.log('Magnetic button initialized successfully');
                } catch(error) {
                    console.error('Error initializing magnetic button:', error);
                }
            } else {
                console.error('initMagneticButton function not available');
            }
            console.log('=== MAGNETIC BUTTON DEBUG END ===');
        });
    </script>
</div>
