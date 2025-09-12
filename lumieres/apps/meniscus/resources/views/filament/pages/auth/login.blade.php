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
            transition: opacity 0.3s ease-out;
            white-space: nowrap;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
            pointer-events: none;
            z-index: 999; /* Below button but visible */
            text-align: center;
        }
    </style>

    <!-- GSAP CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    
    <!-- Texte stationnaire pour Fresnel -->
    <div class="fresnel-label">
        Fresnel
    </div>
    
    <!-- Bouton flottant vers Fresnel avec effet magnétique -->
    <a href="http://fresnel.localhost/panel/admin/login" class="magnetic-button floating-nav-arrow" title="Aller sur Fresnel">
        <div class="arrow-container">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z" clip-rule="evenodd" />
            </svg>
        </div>
    </a>
    
    <script>
        /**
         * GSAP Magnetic Button Effect for Meniscus
         */
        
        // Reset function with elastic animation
        function resetMagnet(button, text) {
          gsap.to(button, {
            x: 0,
            y: 0,
            duration: 2.5,
            ease: "elastic.out(1.2, 0.2)"
          });
        
          // Only animate text if it exists (we removed text from button)
          if (text) {
            gsap.to(text, {
              x: 0,
              y: 0,
              duration: 0.5,
              ease: "power3.out"
            });
          }
        
          // Ensure indicator is visible when button resets
          const indicator = document.querySelector('.fresnel-label');
          if (indicator) {
            gsap.to(indicator, {
              opacity: 1,
              duration: 0.5,
              ease: "power3.out"
            });
          }
        }
        
        const magneticRadius = 100; // Reduced radius for more precise control
        
        // Initialize magnetic button functionality
        function initMagneticButton() {
          console.log('Initializing GSAP magnetic button for Meniscus...');
          
          // Check if GSAP is available
          if (typeof gsap === 'undefined') {
            console.error('GSAP library not found! Please include GSAP.');
            return;
          }
          
          // Get the text label once (it's outside the button)
          const indicator = document.querySelector(".fresnel-label");
          
          // Attach the event listener to the document for better performance
          document.addEventListener("mousemove", (e) => {
            // Loop through all buttons only when necessary
            const buttons = document.querySelectorAll(".magnetic-button");
        
            buttons.forEach((button) => {
              const rect = button.getBoundingClientRect();
              const buttonCenterX = rect.left + rect.width / 2;
              const buttonCenterY = rect.top + rect.height / 2;
        
              const distanceX = e.clientX - buttonCenterX;
              const distanceY = e.clientY - buttonCenterY;
              const distance = Math.sqrt(distanceX ** 2 + distanceY ** 2);
        
              const text = button.querySelector("span");
        
              if (distance < magneticRadius) {
                const offsetX = e.clientX - rect.left - rect.width / 2;
                const offsetY = e.clientY - rect.top - rect.height / 2;
        
                // Text disappears immediately when magnetic effect starts
                let textOpacity = 0; // Text is invisible when in magnetic zone
        
                // Move button with GSAP
                gsap.to(button, {
                  x: offsetX * 0.5,
                  y: offsetY * 0.5,
                  duration: 0.5,
                  ease: "power3.out",
                  overwrite: "auto" // Allow overwriting previous animations
                });
        
                // Hide the indicator text immediately when magnetic effect starts
                if (indicator) {
                  gsap.to(indicator, {
                    opacity: textOpacity,
                    duration: 0.2, // Faster fade out
                    ease: "power3.out",
                    overwrite: "auto"
                  });
                }
        
                // Move other text with GSAP (if it exists - but we removed text from button)
                if (text) {
                  gsap.to(text, {
                    x: offsetX * 0.2,
                    y: offsetY * 0.2,
                    duration: 0.5,
                    ease: "power3.out",
                    overwrite: "auto"
                  });
                }
              } else {
                // Reset indicator opacity when outside magnetic radius
                if (indicator) {
                  gsap.to(indicator, {
                    opacity: 1,
                    duration: 0.5,
                    ease: "power3.out",
                    overwrite: "auto"
                  });
                }
        
                // Throttle the resetMagnet function so it's not triggered too often
                if (!button.resetTimer) {
                  button.resetTimer = setTimeout(() => {
                    resetMagnet(button, text);
                    button.resetTimer = null; // Reset the timer after execution
                  }, 200); // Throttle reset to be triggered only once every 200ms
                }
              }
            });
          });
          
          console.log('GSAP magnetic button for Meniscus initialized successfully');
        }
        
        // Initialize magnetic button behavior when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== MAGNETIC BUTTON MENISCUS DEBUG START ===');
            console.log('DOM loaded, checking GSAP...', typeof gsap);
            console.log('Checking initMagneticButton...', typeof initMagneticButton);
            console.log('Magnetic elements found:', document.querySelectorAll('.magnetic-button').length);
            console.log('Button element:', document.querySelector('.magnetic-button'));
            
            if (typeof initMagneticButton === 'function') {
                console.log('Initializing magnetic button behavior...');
                
                try {
                    initMagneticButton();
                    console.log('Magnetic button initialized successfully');
                } catch(error) {
                    console.error('Error initializing magnetic button:', error);
                }
            } else {
                console.error('initMagneticButton function not available');
            }
            console.log('=== MAGNETIC BUTTON MENISCUS DEBUG END ===');
        });
    </script>
</x-filament-panels::page.simple>
