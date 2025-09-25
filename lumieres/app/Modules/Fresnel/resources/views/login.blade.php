<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - DCPrism</title>
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
        
        /* Spots de lumière RGB - Style cinéma numérique avec optimisations GPU */
        .rgb-light-spot {
            position: fixed;
            width: 2800px; /* Augmenté de 2200px */
            height: 2800px; /* Augmenté de 2200px */
            border-radius: 50%;
            opacity: 1.0; /* Augmenté pour plus d'impact */
            mix-blend-mode: screen;
            pointer-events: none;
            will-change: transform;
            transform: translateZ(0); /* GPU acceleration */
        }
        
        /* Compensation spéciale pour le bleu qui paraît plus faible */
        .blue-light {
            opacity: 1.0; /* Peut être surchargé si nécessaire */
        }
        
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
        
        .rgb-light-spot::after {
            content: '';
            position: absolute;
            top: 5%;
            left: 5%;
            width: 90%;
            height: 90%;
            border-radius: 50%;
            background: inherit;
            filter: blur(240px); /* Réduit de 280px à 240px */
            opacity: 0.4;
            transform: scale(1.1) translateZ(0);
            mix-blend-mode: color-dodge;
            pointer-events: none;
        }
        
        .red-light {
            background: radial-gradient(circle, 
                rgba(255, 31, 31, 1.0) 0%, /* DCI-P3 Rouge authentique */
                rgba(255, 41, 21, 0.9) 25%,
                rgba(255, 61, 41, 0.7) 45%, 
                rgba(255, 31, 11, 0.5) 65%, 
                transparent 80%);
            top: 25%;
            left: 25%;
            transition: transform 10s ease-in-out; /* Transition fluide pour mouvement JS */
            filter: blur(240px); /* Réduit légèrement de 280px */
            /* Suppression du drop-shadow coûteux */
        }
        
        .green-light {
            background: radial-gradient(circle, 
                rgba(31, 255, 31, 1.0) 0%, /* DCI-P3 Vert authentique */
                rgba(21, 255, 41, 0.9) 25%,
                rgba(41, 255, 61, 0.7) 45%, 
                rgba(11, 255, 31, 0.5) 65%, 
                transparent 80%);
            top: 30%;
            right: 25%;
            transition: transform 10s ease-in-out; /* Transition fluide pour mouvement JS */
            filter: blur(240px); /* Réduit légèrement de 280px */
            /* Suppression du drop-shadow coûteux */
        }
        
        .blue-light {
            background: radial-gradient(circle, 
                rgba(31, 31, 255, 1.0) 0%, /* DCI-P3 Bleu authentique */
                rgba(21, 41, 255, 0.95) 25%, /* Renforcé pour compenser la perception */
                rgba(41, 61, 255, 0.8) 45%, /* Renforcé pour compenser la perception */
                rgba(11, 31, 255, 0.6) 65%, /* Renforcé pour compenser la perception */
                rgba(31, 31, 255, 0.3) 75%, /* Couche supplémentaire */
                transparent 85%);
            bottom: -10%; /* Encore plus bas, sort légèrement */
            left: 10%; /* Position gauche modérée */
            transition: transform 10s ease-in-out; /* Transition fluide pour mouvement JS */
            filter: blur(250px); /* Légèrement augmenté pour plus d'impact */
            /* Suppression du drop-shadow coûteux */
        }
        
        
        .glass-login-card {
            position: relative;
            width: 100%;
            max-width: 36rem;
            padding: 3rem;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.3);
            z-index: 10;
            margin: 0 auto;
        }
        
        .glass-login-card input {
            background: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: white !important;
            backdrop-filter: blur(10px);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            width: 100%;
            margin-bottom: 1rem;
        }
        
        .glass-login-card input:focus {
            border-color: rgba(255, 255, 255, 0.4) !important;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.1) !important;
            outline: none;
        }
        
        .glass-login-card input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .glass-login-card button {
            background: rgba(59, 130, 246, 0.5) !important;
            border: 1px solid rgba(59, 130, 246, 0.7) !important;
            backdrop-filter: blur(15px);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            width: 100%;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8);
        }
        
        .glass-login-card button:hover {
            background: rgba(59, 130, 246, 0.7) !important;
            border-color: rgba(59, 130, 246, 0.9) !important;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.5);
        }
        
        .glass-login-card button:active {
            background: rgba(59, 130, 246, 0.8) !important;
            transform: translateY(0);
        }
        
        .glass-login-card label {
            color: rgba(255, 255, 255, 0.9) !important;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .dcprism-logo-header {
            position: fixed;
            top: 2rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1001;
            text-align: center;
        }
        
        .dcprism-logo-header img {
            width: 3rem;
            height: 3rem;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h1 {
            text-align: center;
            font-size: 1.125rem;
            font-weight: 400;
            letter-spacing: -0.025em;
            color: rgba(255, 255, 255, 0.8) !important;
            margin: 0 0 0.25rem 0;
        }
        
        .login-header p {
            margin-top: 0;
            text-align: center;
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            color: rgba(255, 255, 255, 0.95) !important;
            margin-bottom: 0;
        }
        
        .test-accounts-container {
            margin-top: 2rem;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .test-accounts-title {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.875rem;
            line-height: 1.25rem;
            font-weight: 500;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .test-accounts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        /* Base pour tous les boutons de test */
        .test-account-btn {
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            line-height: 1rem;
            font-weight: 600;
            color: white !important;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(15px);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8);
        }
        
        /* Couleurs spécifiques avec double classe pour plus de spécificité */
        .test-account-btn.admin-btn {
            background: rgba(220, 38, 127, 0.3) !important;
            border: 1px solid rgba(220, 38, 127, 0.5) !important;
        }
        
        .test-account-btn.admin-btn:hover {
            background: rgba(220, 38, 127, 0.5) !important;
            border-color: rgba(220, 38, 127, 0.7) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 38, 127, 0.4);
        }
        
        .test-account-btn.tech-btn {
            background: rgba(14, 165, 233, 0.3) !important;
            border: 1px solid rgba(14, 165, 233, 0.5) !important;
        }
        
        .test-account-btn.tech-btn:hover {
            background: rgba(14, 165, 233, 0.5) !important;
            border-color: rgba(14, 165, 233, 0.7) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.4);
        }
        
        .test-account-btn.manager-btn {
            background: rgba(16, 185, 129, 0.3) !important;
            border: 1px solid rgba(16, 185, 129, 0.5) !important;
        }
        
        .test-account-btn.manager-btn:hover {
            background: rgba(16, 185, 129, 0.5) !important;
            border-color: rgba(16, 185, 129, 0.7) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }
        
        .test-account-btn.supervisor-btn {
            background: rgba(139, 92, 246, 0.3) !important;
            border: 1px solid rgba(139, 92, 246, 0.5) !important;
        }
        
        .test-account-btn.supervisor-btn:hover {
            background: rgba(139, 92, 246, 0.5) !important;
            border-color: rgba(139, 92, 246, 0.7) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
        }
        
        .test-account-btn.source-btn {
            background: rgba(234, 88, 12, 0.3) !important;
            border: 1px solid rgba(234, 88, 12, 0.5) !important;
        }
        
        .test-account-btn.source-btn:hover {
            background: rgba(234, 88, 12, 0.5) !important;
            border-color: rgba(234, 88, 12, 0.7) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(234, 88, 12, 0.4);
        }
        
        .test-account-btn.cinema-btn {
            background: rgba(79, 70, 229, 0.3) !important;
            border: 1px solid rgba(79, 70, 229, 0.5) !important;
        }
        
        .test-account-btn.cinema-btn:hover {
            background: rgba(79, 70, 229, 0.5) !important;
            border-color: rgba(79, 70, 229, 0.7) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
        }
        
        .test-accounts-help {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.75rem;
            line-height: 1rem;
            text-align: center;
            margin: 0;
        }

        .error-message {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: rgba(255, 255, 255, 0.9);
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        
        /* Bouton flottant de navigation vers Meniscus - magnetic button */
        .floating-nav-arrow {
            position: fixed;
            top: 50%;
            right: 80px;
            margin-top: -35px;
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
            transform: scale(1.1);
        }
        
        /* Magnetic button specific styles */
        .magnetic-button {
            transition: transform 0.1s ease-out;
            will-change: transform;
        }
        
        .magnetic-button:hover {
            background: rgba(255, 255, 255, 0.3) !important;
            border-color: rgba(255, 255, 255, 0.6) !important;
            box-shadow: 
                0 0 30px rgba(255, 255, 255, 0.4),
                0 0 60px rgba(255, 255, 255, 0.2),
                0 0 90px rgba(255, 255, 255, 0.1);
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
            right: 115px;
            margin-top: -80px;
            transform: translateX(50%);
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            font-weight: 700;
            transition: opacity 0.3s ease-out;
            white-space: nowrap;
            pointer-events: none;
            z-index: 999;
            text-align: center;
            opacity: 0.7;
        }
        
        /* Effets Fresnel subtils sur les spots lumineux */
        .rgb-light-spot::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 120%;
            height: 120%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            pointer-events: none;
            opacity: 0.3;
        }
        
        .rgb-light-spot::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 140%;
            height: 140%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            pointer-events: none;
            opacity: 0.2;
        }
        
        .red-light::before {
            background: rgba(255, 100, 100, 0.1);
            box-shadow: 0 0 30px rgba(255, 0, 0, 0.3);
            animation: simplePulse 8s ease-in-out infinite;
        }
        
        .red-light::after {
            background: rgba(255, 150, 100, 0.05);
            animation: simplePulse 12s ease-in-out infinite 4s;
        }
        
        .green-light::before {
            background: rgba(100, 255, 100, 0.1);
            box-shadow: 0 0 30px rgba(0, 255, 0, 0.3);
            animation: simplePulse 10s ease-in-out infinite;
        }
        
        .green-light::after {
            background: rgba(150, 255, 100, 0.05);
            animation: simplePulse 14s ease-in-out infinite 6s;
        }
        
        .blue-light::before {
            background: rgba(100, 100, 255, 0.1);
            box-shadow: 0 0 30px rgba(0, 0, 255, 0.3);
            animation: simplePulse 12s ease-in-out infinite;
        }
        
        .blue-light::after {
            background: rgba(150, 150, 255, 0.05);
            animation: simplePulse 16s ease-in-out infinite 8s;
        }
        
        @keyframes simplePulse {
            0%, 100% {
                opacity: 0.3;
            }
            50% {
                opacity: 0.7;
            }
        }
        
        /* Aberrations chromatiques légères */
        .red-aberration {
            background: rgba(255, 0, 0, 0.03);
            animation: chromaPulse 20s ease-in-out infinite;
        }
        
        .green-aberration {
            background: rgba(0, 255, 0, 0.03);
            animation: chromaPulse 25s ease-in-out infinite 5s;
        }
        
        .blue-aberration {
            background: rgba(0, 0, 255, 0.03);
            animation: chromaPulse 30s ease-in-out infinite 10s;
        }
        
        @keyframes chromaPulse {
            0%, 100% {
                opacity: 0.1;
            }
            50% {
                opacity: 0.3;
            }
        }
        
        }
        
        /* Aberrations chromatiques légères - Optimisées pour CPU */
        .chromatic-aberration {
            position: fixed;
            width: 1000px; /* Augmenté de 800px */
            height: 1000px; /* Augmenté de 800px */
            border-radius: 50%;
            opacity: 0.15;
            mix-blend-mode: multiply;
            pointer-events: none;
            filter: blur(140px); /* Augmenté de 120px */
        }
        
        .red-aberration {
            background: radial-gradient(circle, 
                rgba(255, 31, 31, 0.4) 0%, /* DCI-P3 Rouge authentique */
                rgba(255, 61, 51, 0.2) 40%,
                transparent 70%);
            top: 20%;
            left: 20%;
            animation: floatRedSmooth 30s ease-in-out infinite 3s;
        }
        
        .green-aberration {
            background: radial-gradient(circle, 
                rgba(31, 255, 31, 0.4) 0%, /* DCI-P3 Vert authentique */
                rgba(51, 255, 61, 0.2) 40%,
                transparent 70%);
            top: 25%;
            right: 20%;
            animation: floatGreenSmooth 35s ease-in-out infinite reverse 5s;
        }
        
        .blue-aberration {
            background: radial-gradient(circle, 
                rgba(31, 31, 255, 0.4) 0%, /* DCI-P3 Bleu authentique */
                rgba(61, 61, 255, 0.2) 40%,
                transparent 70%);
            bottom: 25%;
            left: 25%;
            animation: floatBlueSmooth 40s ease-in-out infinite 7s;
        }
        
        /* Effet Fresnel authentique - Lentilles sur chaque spot RGB - Visibilité améliorée */
        .fresnel-lens {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 1000px; /* Augmenté de 800px */
            height: 1000px; /* Augmenté de 800px */
            transform: translate(-50%, -50%);
            pointer-events: none;
            opacity: 0.8; /* Augmenté pour être visible */
            z-index: 8;
        }
        
        .fresnel-rings {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: 
                /* Anneaux de Fresnel avec visibilité améliorée */
                radial-gradient(circle, transparent 10%, rgba(255,255,255,0.3) 12%, transparent 14%),
                radial-gradient(circle, transparent 18%, rgba(255,255,255,0.25) 20%, transparent 22%),
                radial-gradient(circle, transparent 26%, rgba(255,255,255,0.3) 28%, transparent 30%),
                radial-gradient(circle, transparent 34%, rgba(255,255,255,0.2) 36%, transparent 38%),
                radial-gradient(circle, transparent 42%, rgba(255,255,255,0.28) 44%, transparent 46%),
                radial-gradient(circle, transparent 50%, rgba(255,255,255,0.18) 52%, transparent 54%),
                radial-gradient(circle, transparent 58%, rgba(255,255,255,0.25) 60%, transparent 62%),
                radial-gradient(circle, transparent 66%, rgba(255,255,255,0.15) 68%, transparent 70%),
                radial-gradient(circle, transparent 74%, rgba(255,255,255,0.22) 76%, transparent 78%),
                radial-gradient(circle, transparent 82%, rgba(255,255,255,0.12) 84%, transparent 86%);
            mix-blend-mode: normal; /* Changement pour être visible */
            animation: fresnelRotate 60s linear infinite;
        }
        
        .fresnel-dispersion {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: 
                /* Dispersion chromatique DCI-P3 authentique */
                radial-gradient(circle, transparent 15%, rgba(255,31,31,0.15) 17%, transparent 19%), /* Rouge DCI-P3 */
                radial-gradient(circle, transparent 25%, rgba(31,255,31,0.12) 27%, transparent 29%), /* Vert DCI-P3 */
                radial-gradient(circle, transparent 35%, rgba(31,31,255,0.18) 37%, transparent 39%), /* Bleu DCI-P3 */
                radial-gradient(circle, transparent 45%, rgba(255,255,31,0.1) 47%, transparent 49%), /* Jaune (R+V) */
                radial-gradient(circle, transparent 55%, rgba(255,31,255,0.15) 57%, transparent 59%), /* Magenta (R+B) */
                radial-gradient(circle, transparent 65%, rgba(31,255,255,0.12) 67%, transparent 69%); /* Cyan (V+B) */
            mix-blend-mode: multiply; /* Plus subtil que screen */
            animation: fresnelDispersion 45s ease-in-out infinite reverse;
        }
        
        .fresnel-center {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 240px; /* Augmenté de 180px */
            height: 240px; /* Augmenté de 180px */
            transform: translate(-50%, -50%);
            background: radial-gradient(circle, 
                rgba(255,255,255,0.4) 0%, 
                rgba(255,255,255,0.2) 40%, 
                transparent 80%);
            border-radius: 50%;
            filter: blur(16px); /* Augmenté de 12px */
            animation: fresnelPulse 8s ease-in-out infinite;
        }
        
        @keyframes fresnelRotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes fresnelDispersion {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.05); }
        }
        
        @keyframes fresnelPulse {
            0%, 100% { opacity: 0.4; transform: translate(-50%, -50%) scale(1); }
            50% { opacity: 0.8; transform: translate(-50%, -50%) scale(1.2); }
        }
        
    </style>
</head>
<body>
    <div class="dcprism-login-container">
        <!-- Spots de lumière RGB avec lentilles de Fresnel intégrées -->
        <div class="rgb-light-spot red-light">
            <div class="fresnel-lens">
                <div class="fresnel-rings"></div>
                <div class="fresnel-dispersion"></div>
                <div class="fresnel-center"></div>
            </div>
        </div>
        
        <div class="rgb-light-spot green-light">
            <div class="fresnel-lens">
                <div class="fresnel-rings"></div>
                <div class="fresnel-dispersion"></div>
                <div class="fresnel-center"></div>
            </div>
        </div>
        
        <div class="rgb-light-spot blue-light">
            <div class="fresnel-lens">
                <div class="fresnel-rings"></div>
                <div class="fresnel-dispersion"></div>
                <div class="fresnel-center"></div>
            </div>
        </div>
        
        <!-- Aberrations chromatiques légères pour effet cinéma -->
        <div class="chromatic-aberration red-aberration"></div>
        <div class="chromatic-aberration green-aberration"></div>
        <div class="chromatic-aberration blue-aberration"></div>
        
        <!-- Logo DCPrism centré en haut -->
        <div class="dcprism-logo-header">
            <img src="{{ asset('images/logo-dcprism.svg') }}" alt="DCPrism" />
        </div>
        
        <!-- Carte de connexion glassmorphism -->
        <div class="glass-login-card">
            <!-- Header de connexion -->
            <div class="login-header">
                <h1>Fresnel</h1>
                <p>Connexion</p>
            </div>
            
            @if ($errors->any())
                <div class="error-message">
                    {{ $errors->first() }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div>
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        placeholder="votre@email.com" 
                        required 
                    />
                </div>
                
                <div>
                    <label for="password">Mot de passe</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="••••••••" 
                        required 
                    />
                </div>
                
                <div style="margin-top: 1.5rem;">
                    <button type="submit">
                        Se connecter
                    </button>
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
                        🎦 Source
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
    </div>
    
    <!-- Texte stationnaire pour Meniscus -->
    <div class="meniscus-label">
        Meniscus
    </div>
    
    <!-- Bouton flottant vers Meniscus avec effet magnétique -->
    <a href="/meniscus/login" class="magnetic-button floating-nav-arrow" title="Aller sur Meniscus">
        <div class="arrow-container">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
            </svg>
        </div>
    </a>
    
    <!-- GSAP CDN -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    
    <script>
        // Système de mouvement pseudo-aléatoire pour les spots RGB
        document.addEventListener('DOMContentLoaded', function() {
            const spots = [
                { element: document.querySelector('.red-light'), maxMove: 200 },
                { element: document.querySelector('.green-light'), maxMove: 250 },
                { element: document.querySelector('.blue-light'), maxMove: 280 } // Accentué pour compenser la perception
            ];
            
            // Générateur aléatoire simple et fiable
            function getRandom() {
                return Math.random();
            }
            
            // Fonction pour limiter les mouvements aux bordures d'écran
            function clampToScreen(value) {
                // Marge plus raisonnable : 200px depuis les bordures
                const margin = 200;
                const maxX = (window.innerWidth / 2) - margin;
                const maxY = (window.innerHeight / 2) - margin;
                const maxValue = Math.min(maxX, maxY);
                
                return Math.max(-maxValue, Math.min(maxValue, value));
            }
            
            function moveSpots() {
                console.log('Moving spots...'); // Debug
                spots.forEach((spot, index) => {
                    if (spot.element) {
                        // Calcul de nouvelles positions aléatoires
                        const baseX = (getRandom() - 0.5) * spot.maxMove;
                        const baseY = (getRandom() - 0.5) * spot.maxMove;
                        
                        // Offsets selon l'index pour éviter superposition (bleu accentué)
                        const offsetMultiplierX = (index === 2) ? 100 : 80; // Bleu (index 2) plus accentué
                        const offsetMultiplierY = (index === 2) ? 80 : 60;  // Bleu (index 2) plus accentué
                        const offsetX = index * offsetMultiplierX * (getRandom() - 0.5);
                        const offsetY = index * offsetMultiplierY * (getRandom() - 0.5);
                        
                        // Application des offsets
                        let finalX = baseX + offsetX;
                        let finalY = baseY + offsetY;
                        
                        console.log(`Spot ${index} BEFORE clamp: baseX=${baseX.toFixed(1)}, baseY=${baseY.toFixed(1)}, offsetX=${offsetX.toFixed(1)}, offsetY=${offsetY.toFixed(1)}, finalX=${finalX.toFixed(1)}, finalY=${finalY.toFixed(1)}`); // Debug
                        
                        // Limitation aux bordures d'écran
                        finalX = clampToScreen(finalX);
                        finalY = clampToScreen(finalY);
                        
                        console.log(`Spot ${index}: moving to ${finalX.toFixed(1)}, ${finalY.toFixed(1)}`); // Debug
                        spot.element.style.transform = `translate(${finalX}px, ${finalY}px) translateZ(0)`;
                    } else {
                        console.log(`Spot ${index}: element not found`); // Debug
                    }
                });
            }
            
            // Démarrage du mouvement
            moveSpots();
            
            // Nouveau mouvement toutes les 20 secondes
            setInterval(moveSpots, 20000);
            
        // Animation magnétique du bouton Meniscus (continue...)
            const magneticButton = document.querySelector('.magnetic-button');
            const meniscusLabel = document.querySelector('.meniscus-label');
            
            if (magneticButton && typeof gsap !== 'undefined') {
                magneticButton.addEventListener('mousemove', function(e) {
                    const rect = magneticButton.getBoundingClientRect();
                    const centerX = rect.left + rect.width / 2;
                    const centerY = rect.top + rect.height / 2;
                    
                    const deltaX = (e.clientX - centerX) * 0.5;
                    const deltaY = (e.clientY - centerY) * 0.5;
                    
                    gsap.to(magneticButton, {
                        x: deltaX,
                        y: deltaY,
                        duration: 0.3,
                        ease: "power2.out"
                    });
                    
                    // Effet sur le texte Meniscus
                    if (meniscusLabel) {
                        meniscusLabel.style.opacity = '1';
                    }
                });
                
                magneticButton.addEventListener('mouseleave', function() {
                    gsap.to(magneticButton, {
                        x: 0,
                        y: 0,
                        duration: 0.5,
                        ease: "power2.out"
                    });
                    
                    // Fade out du texte Meniscus
                    if (meniscusLabel) {
                        setTimeout(() => {
                            meniscusLabel.style.opacity = '0.7';
                        }, 200);
                    }
                });
            }
        });
        
        function fillCredentials(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
            
            // Effet visuel
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            
            emailInput.style.borderColor = 'rgba(34, 197, 94, 0.6)';
            passwordInput.style.borderColor = 'rgba(34, 197, 94, 0.6)';
            
            setTimeout(() => {
                emailInput.style.borderColor = 'rgba(255, 255, 255, 0.2)';
                passwordInput.style.borderColor = 'rgba(255, 255, 255, 0.2)';
            }, 1000);
        }
    </script>
</body>
</html>
