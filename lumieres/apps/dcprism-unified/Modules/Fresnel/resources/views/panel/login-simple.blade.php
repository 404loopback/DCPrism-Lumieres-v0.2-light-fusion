<div class="dcprism-login-container">
    {!! \Filament\Support\Facades\FilamentAsset::renderStyles() !!}
    {!! \Filament\Support\Facades\FilamentAsset::renderScripts() !!}
    @livewireStyles
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    
    <style>
        body {
            background: #0a0a0a;
            min-height: 100vh;
            font-family: 'Figtree', sans-serif;
            position: relative;
            overflow: hidden;
        }
        
        .dcprism-login-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: #0a0a0a;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        
        /* Spots de lumière RGB qui se croisent - Style cinéma numérique */
        .rgb-light-spot {
            position: fixed;
            width: 2200px;
            height: 2200px;
            border-radius: 50%;
            opacity: 0.95; /* Légèrement moins opaque pour plus de subtilité */
            mix-blend-mode: screen;
        }
        
        /* Effets de reflets d'objectifs de projection */
        .rgb-light-spot::before {
            content: '';
            position: absolute;
            top: 10%;
            left: 10%;
            width: 80%;
            height: 80%;
            border-radius: 50%;
            background: radial-gradient(circle, 
                rgba(255, 255, 255, 0.15) 0%, 
                rgba(255, 255, 255, 0.08) 30%, 
                transparent 60%);
            mix-blend-mode: overlay;
            pointer-events: none;
        }
        
        /* Aberration chromatique supplémentaire */
        .rgb-light-spot::after {
            content: '';
            position: absolute;
            top: 5%;
            left: 5%;
            width: 90%;
            height: 90%;
            border-radius: 50%;
            background: inherit;
            filter: blur(320px);
            opacity: 0.4;
            transform: scale(1.1);
            mix-blend-mode: color-dodge;
            pointer-events: none;
        }
        
        .red-light {
            /* Rouge cinéma avec température plus chaude et aberration chromatique */
            background: radial-gradient(circle, 
                rgba(255, 45, 25, 1.0) 0%, 
                rgba(255, 35, 15, 0.9) 25%,
                rgba(255, 60, 40, 0.7) 45%, 
                rgba(255, 20, 5, 0.5) 65%, 
                transparent 80%);
            top: 10%;
            left: 10%;
            animation: floatRed 25s ease-in-out infinite;
            /* Effet d'aberration chromatique */
            filter: blur(280px) drop-shadow(15px 15px 40px rgba(255, 100, 80, 0.3));
        }
        
        .green-light {
            /* Vert cinéma avec nuance plus réaliste */
            background: radial-gradient(circle, 
                rgba(25, 255, 45, 1.0) 0%, 
                rgba(15, 255, 35, 0.9) 25%,
                rgba(40, 255, 60, 0.7) 45%, 
                rgba(5, 255, 20, 0.5) 65%, 
                transparent 80%);
            top: 15%;
            right: 10%;
            animation: floatGreen 30s ease-in-out infinite reverse;
            /* Effet d'aberration chromatique */
            filter: blur(280px) drop-shadow(-15px 15px 40px rgba(80, 255, 100, 0.3));
        }
        
        .blue-light {
            /* Bleu cinéma avec température plus froide */
            background: radial-gradient(circle, 
                rgba(25, 45, 255, 1.0) 0%, 
                rgba(15, 35, 255, 0.9) 25%,
                rgba(40, 60, 255, 0.7) 45%, 
                rgba(5, 20, 255, 0.5) 65%, 
                transparent 80%);
            bottom: 15%;
            left: 15%;
            animation: floatBlue 35s ease-in-out infinite;
            /* Effet d'aberration chromatique */
            filter: blur(280px) drop-shadow(15px -15px 40px rgba(80, 100, 255, 0.3));
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
        
        /* Conteneur de connexion avec dimensions Filament classiques */
        .glass-login-card {
            position: relative;
            width: 100%;
            max-width: 36rem; /* 576px - container-xl Filament pour plus de largeur */
            padding: 3rem; /* 48px - padding plus généreux comme Filament */
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem; /* 8px - radius-lg standard Filament */
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.3);
            z-index: 10;
            margin: 0 auto;
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
            transition: opacity 0.2s ease-out; /* Plus rapide */
            white-space: nowrap;
            pointer-events: none;
            z-index: 999; /* Below button but visible */
            text-align: center;
        }
        
        /* Logo DCPrism en haut comme sur Meniscus */
        .dcprism-logo-header {
            position: fixed;
            top: 2rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1001;
            text-align: center;
        }
        
        .dcprism-logo-header img {
            width: 3rem; /* 48px */
            height: 3rem; /* 48px */
        }
        
        /* Header de connexion dans la boîte */
        .login-header {
            text-align: center;
            margin-bottom: 2rem; /* 32px - espacement standard Filament */
        }
        
        .login-header h1 {
            /* Fresnel - plus petit et léger */
            text-align: center;
            font-size: 1.125rem; /* 18px - plus petit */
            font-weight: 400; /* font-normal - léger */
            letter-spacing: -0.025em;
            color: rgba(255, 255, 255, 0.8) !important; /* moins opaque */
            margin: 0 0 0.25rem 0;
        }
        
        .login-header p {
            /* Connexion - titre principal - gros et dense */
            margin-top: 0;
            text-align: center;
            font-size: 1.75rem; /* 28px - plus gros que Fresnel */
            font-weight: 700; /* font-bold - dense et gras */
            letter-spacing: -0.025em;
            color: rgba(255, 255, 255, 0.95) !important; /* plus opaque */
            margin-bottom: 0;
        }
        
        /* Styles pour les comptes de test avec espacement Filament */
        .test-accounts-container {
            margin-top: 2rem; /* 32px - espacement standard Filament */
            padding: 1.5rem; /* 24px */
            background: rgba(255, 255, 255, 0.05);
            border-radius: 0.75rem; /* 12px - border-radius standard Filament */
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .test-accounts-title {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.875rem; /* 14px - text-sm Filament */
            line-height: 1.25rem; /* 20px */
            font-weight: 500; /* font-medium */
            margin-bottom: 1rem; /* 16px - espacement standard Filament */
            text-align: center;
        }
        
        .test-accounts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem; /* 8px */
            margin-bottom: 1rem; /* 16px - espacement standard Filament */
        }
        
        .test-account-btn {
            padding: 0.5rem 0.75rem; /* 8px 12px */
            border: none;
            border-radius: 0.5rem; /* 8px - radius-lg standard Filament */
            font-size: 0.75rem; /* 12px - text-xs Filament */
            line-height: 1rem; /* 16px */
            font-weight: 500; /* font-medium */
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
            font-size: 0.75rem; /* 12px - text-xs Filament */
            line-height: 1rem; /* 16px */
            text-align: center;
            margin: 0;
        }
        
        /* Effets de dispersion chromatique supplémentaires pour cinéma */
        .chromatic-aberration {
            position: fixed;
            width: 1800px;
            height: 1800px;
            border-radius: 50%;
            opacity: 0.3;
            mix-blend-mode: multiply;
            pointer-events: none;
            filter: blur(400px);
        }
        
        .red-aberration {
            background: radial-gradient(circle, rgba(255, 0, 0, 0.8) 0%, transparent 70%);
            top: 15%;
            left: 15%;
            animation: floatRed 25s ease-in-out infinite 2s; /* Décalé de 2s */
        }
        
        .green-aberration {
            background: radial-gradient(circle, rgba(0, 255, 0, 0.8) 0%, transparent 70%);
            top: 20%;
            right: 15%;
            animation: floatGreen 30s ease-in-out infinite reverse 3s; /* Décalé de 3s */
        }
        
        .blue-aberration {
            background: radial-gradient(circle, rgba(0, 0, 255, 0.8) 0%, transparent 70%);
            bottom: 20%;
            left: 20%;
            animation: floatBlue 35s ease-in-out infinite 4s; /* Décalé de 4s */
        }
        }
        
        .dcprism-login-card {
            width: 100%;
            max-width: 36rem;
            padding: 3rem;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.3);
        }
        
        .dcprism-login-card .fi-form {
            color: white;
        }
        
        .dcprism-login-card .fi-input {
            background: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: white !important;
            backdrop-filter: blur(10px);
        }
        
        .dcprism-login-card .fi-input:focus {
            border-color: rgba(255, 255, 255, 0.4) !important;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.1) !important;
        }
        
        .dcprism-login-card .fi-btn-primary {
            background: rgba(255, 255, 255, 0.15) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            backdrop-filter: blur(10px);
        }
        
        .dcprism-login-card .fi-btn-primary:hover {
            background: rgba(255, 255, 255, 0.25) !important;
            border-color: rgba(255, 255, 255, 0.4) !important;
        }
        
        .dcprism-login-card label {
            color: rgba(255, 255, 255, 0.9) !important;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h1 {
            font-size: 1.125rem;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.8) !important;
            margin: 0 0 0.25rem 0;
        }
        
        .login-header p {
            font-size: 1.75rem;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.95) !important;
            margin: 0;
        }
    </style>
    
    <!-- Spots de lumière RGB animés avec effets cinéma -->
    <div class="rgb-light-spot red-light"></div>
    <div class="rgb-light-spot green-light"></div>
    <div class="rgb-light-spot blue-light"></div>
    
    <div class="dcprism-login-card">
        <div class="login-header">
            <img src="{{ asset('images/logo-dcprism.svg') }}" alt="DCPrism" style="width: 3rem; height: 3rem; margin: 0 auto 1rem;" />
            <h1>Fresnel</h1>
            <p>Connexion</p>
        </div>
        
        <form wire:submit="authenticate">
            {{ $this->form }}
            
            <div style="margin-top: 1.5rem;">
                <x-filament::button type="submit" size="lg" class="w-full">
                    Se connecter
                </x-filament::button>
            </div>
        </form>
        
        <!-- Comptes de test -->
        <div style="margin-top: 2rem; padding: 1.5rem; background: rgba(255, 255, 255, 0.05); border-radius: 0.75rem; border: 1px solid rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px);">
            <p style="color: rgba(255, 255, 255, 0.9); font-size: 0.875rem; font-weight: 500; margin-bottom: 1rem; text-align: center;">
                <strong>Comptes de test :</strong>
            </p>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem; margin-bottom: 1rem;">
                <button type="button" onclick="fillCredentials('admin@dcprism.local', 'admin123')" style="padding: 0.5rem 0.75rem; border: none; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 500; color: white; cursor: pointer; background: rgba(239, 68, 68, 0.7); border: 1px solid rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); transition: all 0.3s ease;" onmouseover="this.style.background='rgba(239, 68, 68, 0.9)'" onmouseout="this.style.background='rgba(239, 68, 68, 0.7)'">
                    👑 Admin
                </button>
                
                <button type="button" onclick="fillCredentials('tech@dcprism.local', 'password')" style="padding: 0.5rem 0.75rem; border: none; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 500; color: white; cursor: pointer; background: rgba(59, 130, 246, 0.7); border: 1px solid rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); transition: all 0.3s ease;" onmouseover="this.style.background='rgba(59, 130, 246, 0.9)'" onmouseout="this.style.background='rgba(59, 130, 246, 0.7)'">
                    🔧 Tech
                </button>
            </div>
            <p style="color: rgba(255, 255, 255, 0.6); font-size: 0.75rem; text-align: center; margin: 0;">
                Cliquez sur un bouton pour remplir automatiquement les champs
            </p>
        </div>
        
        <script>
            function fillCredentials(email, password) {
                const emailInput = document.querySelector('input[name="data.email"]') || document.querySelector('input[type="email"]');
                const passwordInput = document.querySelector('input[name="data.password"]') || document.querySelector('input[type="password"]');
                
                if (emailInput && passwordInput) {
                    emailInput.value = email;
                    passwordInput.value = password;
                    
                    ['input', 'change', 'keyup', 'blur'].forEach(eventType => {
                        emailInput.dispatchEvent(new Event(eventType, { bubbles: true }));
                        passwordInput.dispatchEvent(new Event(eventType, { bubbles: true }));
                    });
                    
                    if (window.Livewire && window.Livewire.first()) {
                        window.Livewire.first().set('data.email', email);
                        window.Livewire.first().set('data.password', password);
                    }
                }
            }
        </script>
        
        <x-filament-actions::modals />
    </div>
</div>
