/**
 * QRCode for JavaScript - Local version
 * 
 * Copied from: https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js
 * If you see this comment, the library needs to be replaced with the actual minified code
 */

// Simple QR Code generator fallback
window.QRCode = function(element, options) {
    // Fallback basic QR code generator
    if (typeof element === 'string') {
        element = document.getElementById(element);
    }
    
    const text = options.text || options;
    const size = options.width || options.height || 256;
    const color = options.colorDark || '#000000';
    const background = options.colorLight || '#FFFFFF';
    
    // Créer une image QR code via API externe en fallback
    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(text)}&color=${color.replace('#', '')}&bgcolor=${background.replace('#', '')}`;
    
    const img = document.createElement('img');
    img.src = qrUrl;
    img.width = size;
    img.height = size;
    img.alt = 'QR Code';
    img.style.display = 'block';
    img.style.margin = '0 auto';
    
    // Vider le conteneur et ajouter l'image
    element.innerHTML = '';
    element.appendChild(img);
    
    return {
        makeCode: function(newText) {
            const newUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(newText)}&color=${color.replace('#', '')}&bgcolor=${background.replace('#', '')}`;
            img.src = newUrl;
        }
    };
};

// Niveaux de correction d'erreur
window.QRCode.CorrectLevel = {
    L: 1,
    M: 0,
    Q: 3,
    H: 2
};

console.log('QRCode fallback library loaded');
