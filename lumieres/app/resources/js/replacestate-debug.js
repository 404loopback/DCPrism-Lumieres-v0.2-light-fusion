// Script de debug avancé pour diagnostiquer le problème replaceState Livewire
console.log('🔍 Advanced ReplaceState Debug started');

const originalReplaceState = window.history.replaceState;
let callCount = 0;
let lastCallTime = Date.now();
let callsInLastSecond = [];
let allCalls = [];
let suspiciousPatterns = [];
let loopDetected = false;

// Fonction pour analyser la stack trace et identifier la source
function analyzeStackTrace(stack) {
    const lines = stack.split('\n');
    const livewireLines = lines.filter(line => 
        line.includes('livewire') || 
        line.includes('Livewire') || 
        line.includes('alpine') ||
        line.includes('filament')
    );
    
    const relevantLines = lines.slice(1, 6).map(line => {
        // Nettoyer la ligne pour extraire l'info utile
        const match = line.match(/at\s+(\w+).*?\((.*?):(\d+):(\d+)\)/) || 
                     line.match(/at\s+(.*?)\s+\((.*?):(\d+):(\d+)\)/) ||
                     line.match(/(\w+)@(.*?):(\d+):(\d+)/);
        if (match) {
            return {
                function: match[1] || 'anonymous',
                file: match[2]?.split('/').pop() || 'unknown',
                line: match[3] || '?',
                column: match[4] || '?'
            };
        }
        return { raw: line.trim() };
    });
    
    return {
        livewireRelated: livewireLines.length > 0,
        livewireLines,
        relevantLines,
        firstRelevant: relevantLines[0]
    };
}

// Fonction pour détecter les patterns de boucle
function detectLoopPattern(calls) {
    if (calls.length < 10) return false;
    
    const recent = calls.slice(-10);
    const urls = recent.map(call => call.args[2]); // L'URL est le 3ème argument
    const intervals = recent.map(call => call.timeSinceLastCall);
    
    // Vérifier si la même URL est appelée répétitivement
    const uniqueUrls = [...new Set(urls)];
    if (uniqueUrls.length === 1) {
        return {
            type: 'SAME_URL_LOOP',
            url: uniqueUrls[0],
            frequency: intervals.filter(i => i < 200).length
        };
    }
    
    // Vérifier les intervalles très courts
    const fastCalls = intervals.filter(i => i < 50).length;
    if (fastCalls > 7) {
        return {
            type: 'RAPID_FIRE_LOOP',
            fastCalls,
            averageInterval: intervals.reduce((a, b) => a + b, 0) / intervals.length
        };
    }
    
    return false;
}

// Surveiller les patterns suspects
setInterval(() => {
    if (allCalls.length > 0) {
        const recent = allCalls.filter(call => Date.now() - call.time < 3000);
        if (recent.length > 15) {
            const pattern = detectLoopPattern(allCalls);
            if (pattern && !loopDetected) {
                loopDetected = true;
                console.error('🚨🚨🚨 LOOP DETECTED!', pattern);
                console.table(recent.slice(-5).map(call => ({
                    count: call.count,
                    interval: call.timeSinceLastCall + 'ms',
                    url: call.args[2],
                    source: call.stackAnalysis.firstRelevant?.function || 'unknown'
                })));
                
                // Arrêter temporairement les appels pour analysis
                console.warn('⏸️ PAUSING replaceState for analysis...');
                setTimeout(() => {
                    console.log('▶️ RESUMING replaceState...');
                    loopDetected = false;
                }, 2000);
            }
        }
    }
}, 1000);

window.history.replaceState = function(...args) {
    const now = Date.now();
    const timeSinceLastCall = now - lastCallTime;
    
    // Si on a détecté une boucle, ignorer temporairement
    if (loopDetected) {
        console.warn('🚫 BLOCKED replaceState during loop analysis');
        return;
    }
    
    callCount++;
    callsInLastSecond.push(now);
    
    // Analyser la stack trace
    const stackTrace = new Error().stack;
    const stackAnalysis = analyzeStackTrace(stackTrace);
    
    // Sauvegarder l'appel avec analyse complète
    const callData = {
        count: callCount,
        time: now,
        timeSinceLastCall: timeSinceLastCall,
        args: args,
        url: args[2],
        stackAnalysis: stackAnalysis,
        isLivewireRelated: stackAnalysis.livewireRelated
    };
    
    allCalls.push(callData);
    
    // Nettoyer les anciens appels
    callsInLastSecond = callsInLastSecond.filter(time => now - time < 1000);
    
    // Log détaillé pour les appels suspects
    if (timeSinceLastCall < 100 || callsInLastSecond.length > 3) {
        console.group(`🚨 SUSPICIOUS replaceState call #${callCount}`);
        console.log('⏱️ Time since last:', timeSinceLastCall + 'ms');
        console.log('🔥 Calls in last second:', callsInLastSecond.length);
        console.log('🎯 URL:', args[2]);
        console.log('🧬 Livewire related:', stackAnalysis.livewireRelated);
        console.log('📍 Source function:', stackAnalysis.firstRelevant?.function || 'unknown');
        console.log('📂 Source file:', stackAnalysis.firstRelevant?.file || 'unknown');
        if (stackAnalysis.livewireLines.length > 0) {
            console.log('⚡ Livewire stack:', stackAnalysis.livewireLines.slice(0, 2));
        }
        console.groupEnd();
    } else {
        // Log normal pour les appels normaux
        console.log(`📞 #${callCount} (${timeSinceLastCall}ms) ${args[2]?.substring(args[2].lastIndexOf('/')) || 'no-url'}`);
    }
    
    lastCallTime = now;
    
    return originalReplaceState.apply(this, args);
};

// Ajouter des hooks pour surveiller Livewire spécifiquement
window.addEventListener('livewire:navigating', (e) => {
    console.log('🔄 Livewire navigating:', e.detail);
});

window.addEventListener('livewire:navigated', (e) => {
    console.log('✅ Livewire navigated:', e.detail);
});

// Surveiller les événements DOM qui pourraient déclencher la boucle
document.addEventListener('DOMContentLoaded', () => {
    console.log('🏁 DOM loaded, watching for Livewire issues...');
    
    // Observer les mutations DOM qui pourraient causer des problèmes
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && 
                (mutation.attributeName === 'wire:navigate' || 
                 mutation.attributeName === 'href')) {
                console.log('🔧 Livewire navigation attribute changed:', mutation.target);
            }
        });
    });
    
    observer.observe(document.body, { 
        attributes: true, 
        subtree: true, 
        attributeFilter: ['wire:navigate', 'href', 'data-livewire-navigate'] 
    });
});
