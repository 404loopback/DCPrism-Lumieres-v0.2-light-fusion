// Debug spécialisé pour la navigation Livewire SPA
console.log('🚀 Livewire Navigation Debug started');

let navigationEvents = [];
let urlChanges = [];
let componentUpdates = [];

// Surveiller tous les événements Livewire liés à la navigation
const livewireEvents = [
    'livewire:init',
    'livewire:start',
    'livewire:navigating', 
    'livewire:navigated',
    'livewire:navigate',
    'livewire:load',
    'livewire:update',
    'livewire:component-updated',
    'livewire:morph'
];

livewireEvents.forEach(eventName => {
    window.addEventListener(eventName, (e) => {
        const timestamp = Date.now();
        const eventData = {
            event: eventName,
            time: timestamp,
            detail: e.detail,
            url: window.location.href,
            pathname: window.location.pathname
        };
        
        navigationEvents.push(eventData);
        
        console.log(`🎯 ${eventName}:`, {
            time: new Date(timestamp).toISOString(),
            url: eventData.pathname,
            detail: e.detail
        });
        
        // Détecter les patterns problématiques
        if (navigationEvents.length > 1) {
            const lastEvent = navigationEvents[navigationEvents.length - 2];
            const timeDiff = timestamp - lastEvent.time;
            
            if (timeDiff < 100 && eventName === lastEvent.event) {
                console.warn(`⚡ RAPID ${eventName} events: ${timeDiff}ms apart`);
            }
        }
    });
});

// Surveiller spécifiquement les changements d'URL
let lastUrl = window.location.href;
const urlObserver = new MutationObserver(() => {
    if (window.location.href !== lastUrl) {
        const change = {
            from: lastUrl,
            to: window.location.href,
            time: Date.now()
        };
        urlChanges.push(change);
        
        console.log('📍 URL Changed:', {
            from: change.from.substring(change.from.lastIndexOf('/')),
            to: change.to.substring(change.to.lastIndexOf('/')),
            time: new Date(change.time).toISOString()
        });
        
        lastUrl = window.location.href;
    }
});

// Observer les changements dans le DOM qui pourraient indiquer des updates
urlObserver.observe(document.body, {
    childList: true,
    subtree: true
});

// Fonction pour analyser les patterns de navigation problématiques
function analyzeNavigationPatterns() {
    const recent = navigationEvents.filter(e => Date.now() - e.time < 5000);
    
    if (recent.length > 10) {
        console.group('🔍 Navigation Pattern Analysis');
        
        // Grouper par type d'événement
        const groupedEvents = recent.reduce((acc, event) => {
            if (!acc[event.event]) acc[event.event] = [];
            acc[event.event].push(event);
            return acc;
        }, {});
        
        Object.keys(groupedEvents).forEach(eventType => {
            const events = groupedEvents[eventType];
            if (events.length > 3) {
                console.warn(`📊 High frequency ${eventType}: ${events.length} times in 5s`);
                
                // Analyser les intervalles
                const intervals = [];
                for (let i = 1; i < events.length; i++) {
                    intervals.push(events[i].time - events[i-1].time);
                }
                const avgInterval = intervals.reduce((a, b) => a + b, 0) / intervals.length;
                console.log(`⏱️ Average interval: ${avgInterval.toFixed(0)}ms`);
            }
        });
        
        // Vérifier les boucles URL
        const recentUrls = urlChanges.filter(c => Date.now() - c.time < 5000);
        if (recentUrls.length > 5) {
            console.warn('🔄 Potential URL loop detected:', recentUrls.length, 'changes');
            console.table(recentUrls.slice(-5).map(c => ({
                from: c.from.substring(c.from.lastIndexOf('/')),
                to: c.to.substring(c.to.lastIndexOf('/'))
            })));
        }
        
        console.groupEnd();
    }
}

// Analyser les patterns toutes les 3 secondes
setInterval(analyzeNavigationPatterns, 3000);

// Hook dans les composants Livewire pour surveiller les mises à jour
document.addEventListener('DOMContentLoaded', () => {
    // Surveiller les composants Livewire
    const livewireComponents = document.querySelectorAll('[wire\\:id]');
    console.log(`📱 Found ${livewireComponents.length} Livewire components`);
    
    // Observer les changements sur chaque composant
    livewireComponents.forEach((component, index) => {
        const componentId = component.getAttribute('wire:id');
        console.log(`🔧 Watching component #${index}: ${componentId}`);
        
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes') {
                    console.log(`🔄 Component ${componentId} attribute changed:`, 
                        mutation.attributeName, mutation.target.getAttribute(mutation.attributeName));
                }
                
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    console.log(`➕ Component ${componentId} DOM updated:`, mutation.addedNodes.length, 'nodes added');
                }
            });
        });
        
        observer.observe(component, {
            attributes: true,
            childList: true,
            subtree: true,
            attributeFilter: ['wire:navigate', 'href', 'wire:click']
        });
    });
});

// Surveiller spécifiquement les liens avec wire:navigate
document.addEventListener('click', (e) => {
    const target = e.target.closest('a[wire\\:navigate], a[data-livewire-navigate]');
    if (target) {
        console.log('🔗 Livewire navigation link clicked:', {
            href: target.href,
            navigate: target.getAttribute('wire:navigate') || target.getAttribute('data-livewire-navigate'),
            text: target.textContent.trim()
        });
    }
});

// Export des données pour inspection manuelle
window.livewireDebugData = {
    navigationEvents: () => navigationEvents,
    urlChanges: () => urlChanges,
    componentUpdates: () => componentUpdates,
    summary: () => {
        console.group('🎯 Livewire Debug Summary');
        console.log('Navigation events:', navigationEvents.length);
        console.log('URL changes:', urlChanges.length);
        console.log('Component updates:', componentUpdates.length);
        console.groupEnd();
        
        return {
            navigationEvents: navigationEvents.length,
            urlChanges: urlChanges.length,
            componentUpdates: componentUpdates.length
        };
    }
};
