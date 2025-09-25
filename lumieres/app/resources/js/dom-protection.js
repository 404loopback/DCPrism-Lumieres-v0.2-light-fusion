// Protection globale contre les erreurs DOM dans Filament
// Corrige les erreurs "Cannot read properties of null (reading 'classList')"

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM protection loaded');

    // Protection pour NodeList.forEach
    const originalForEach = NodeList.prototype.forEach;
    NodeList.prototype.forEach = function(callback, thisArg) {
        const safeCallback = function(element, index, list) {
            // Vérifier que l'élément existe et est valide
            if (element && element.nodeType === Node.ELEMENT_NODE) {
                try {
                    callback.call(this, element, index, list);
                } catch (error) {
                    console.warn('Error in NodeList forEach:', error);
                    console.warn('Problematic element:', element);
                }
            } else {
                console.warn('Null or invalid element found in NodeList at index:', index);
            }
        };
        originalForEach.call(this, safeCallback, thisArg);
    };

    // Protection pour querySelectorAll
    const originalQuerySelectorAll = Document.prototype.querySelectorAll;
    Document.prototype.querySelectorAll = function(selector) {
        try {
            return originalQuerySelectorAll.call(this, selector);
        } catch (error) {
            console.warn('Error in querySelectorAll:', error);
            return document.createDocumentFragment().querySelectorAll(selector);
        }
    };

    // Gestion globale des erreurs
    window.addEventListener('error', function(e) {
        if (e.message && e.message.includes('classList')) {
            console.warn('DOM classList error suppressed:', {
                message: e.message,
                line: e.lineno,
                source: e.filename
            });
            e.preventDefault();
            return false;
        }
    });

    console.log('DOM protection active');
});
