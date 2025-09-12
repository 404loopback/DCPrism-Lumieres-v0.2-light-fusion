<x-filament-panels::page.simple>
    {{ $this->content }}

    <!-- CSS pour le bouton flottant -->
    <style>
        .floating-nav-arrow {
            position: fixed;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            z-index: 1000;
            animation: pulse 2s infinite;
        }
        
        .floating-nav-arrow:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
            transform: translateY(-50%) scale(1.1);
            color: white;
            text-decoration: none;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 0.8;
                transform: translateY(-50%) scale(1);
            }
            50% {
                opacity: 1;
                transform: translateY(-50%) scale(1.05);
            }
        }
        
        .floating-nav-arrow svg {
            width: 20px;
            height: 20px;
        }
    </style>

    <!-- Bouton flottant vers Fresnel (flèche gauche) -->
    <a href="http://fresnel.localhost/panel/admin/login" class="floating-nav-arrow" title="Aller sur Fresnel">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z" clip-rule="evenodd" />
        </svg>
    </a>
</x-filament-panels::page.simple>
