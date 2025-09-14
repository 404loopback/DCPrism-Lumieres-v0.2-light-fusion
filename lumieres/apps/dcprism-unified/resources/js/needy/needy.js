/**
 * GSAP Magnetic Button Effect
 * Smooth magnetic attraction with elastic reset animation
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
  const indicator = document.querySelector('.meniscus-label');
  if (indicator) {
    gsap.to(indicator, {
      opacity: 1,
      duration: 0.35, // Légèrement plus rapide
      ease: "power3.out"
    });
  }
}

const magneticRadius = 100; // Reduced radius for more precise control

// Initialize magnetic button functionality
function initMagneticButton() {
  console.log('Initializing GSAP magnetic button...');
  
  // Check if GSAP is available
  if (typeof gsap === 'undefined') {
    console.error('GSAP library not found! Please include GSAP.');
    return;
  }
  
  // Get the text label once (it's outside the button)
  const indicator = document.querySelector(".meniscus-label");
  
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
            duration: 0.15, // Légèrement plus rapide
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
            duration: 0.35, // Légèrement plus rapide
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
  
  console.log('GSAP magnetic button initialized successfully');
}

// Make available globally
if (typeof window !== 'undefined') {
  window.initMagneticButton = initMagneticButton;
  // Backward compatibility
  window.initNeedy = initMagneticButton;
}
