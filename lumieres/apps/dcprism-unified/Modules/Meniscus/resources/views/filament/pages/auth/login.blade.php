<x-filament-panels::page.simple>
    {{ $this->content }}

    <!-- CSS pour le bouton flottant magnétique -->
    <style>
        /* Bouton flottant de navigation vers Fresnel - magnetic button */
        .floating-nav-arrow {
            position: fixed;
            top: 50%;
            left: 20px;
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
        
        /* Texte stationnaire Fresnel - ne bouge jamais */
        .fresnel-label {
            position: fixed;
            top: 50%;
            left: 55px; /* Button left (20px) + button width/2 (35px) */
            margin-top: -80px; /* Button height/2 + 45px spacing above */
            transform: translateX(-50%); /* Center horizontally above button */
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            font-weight: 700;
            transition: opacity 0.2s ease-out; /* Plus rapide */
            white-space: nowrap;
            /* Ombre portée supprimée */
            pointer-events: none;
            z-index: 999; /* Below button but visible */
            text-align: center;
        }
        
        /* Logo DCPrism centré au-dessus */
        .dcprism-logo {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1001;
        }
        
        .dcprism-logo img {
            width: 48px;
            height: 48px;
        }
    </style>

    <!-- GSAP CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    
    <!-- Logo DCPrism centré en haut -->
    <div class="dcprism-logo">
        <img src="{{ asset('images/logo-dcprism.svg') }}" alt="DCPrism" />
    </div>
    
    <!-- Texte stationnaire pour Fresnel -->
    <div class="fresnel-label">
        Fresnel
    </div>
    
    <!-- Bouton flottant vers Fresnel avec effet magnétique -->
    <a href="{{ route('filament.fresnel.auth.login') }}" class="magnetic-button floating-nav-arrow" title="Aller sur Fresnel">
        <div class="arrow-container">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z" clip-rule="evenodd" />
            </svg>
        </div>
    </a>
    
    <script type="text/javascript">
        // Animation magnétique du bouton Fresnel (style simple et efficace)
        document.addEventListener('DOMContentLoaded', function() {
            const magneticButton = document.querySelector('.magnetic-button');
            const fresnelLabel = document.querySelector('.fresnel-label');
            
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
                    
                    // Effet sur le texte Fresnel
                    if (fresnelLabel) {
                        fresnelLabel.style.opacity = '1';
                    }
                });
                
                magneticButton.addEventListener('mouseleave', function() {
                    gsap.to(magneticButton, {
                        x: 0,
                        y: 0,
                        duration: 0.5,
                        ease: "power2.out"
                    });
                    
                    // Fade out du texte Fresnel
                    if (fresnelLabel) {
                        setTimeout(() => {
                            fresnelLabel.style.opacity = '0.9';
                        }, 200);
                    }
                });
            }
        });
    </script>
</x-filament-panels::page.simple>
