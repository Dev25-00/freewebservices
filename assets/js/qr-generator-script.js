// Script principal pour le générateur de QR codes
// services/qr-generator-script.js

document.addEventListener('DOMContentLoaded', function() {
    console.log('QR Generator: Initializing with dynamic CDN loading...');
    
    // Charger la bibliothèque QRCode dynamiquement
    loadQRCodeLibrary().then(() => {
        console.log('QR Generator: Library loaded successfully');
        initQRGenerator();
    }).catch((error) => {
        console.error('QR Generator: Failed to load library:', error);
        showError('Erreur de chargement. Le générateur utilisera un service externe.');
        // Initialiser quand même avec le fallback
        initQRGenerator();
    });
});

// Charger la bibliothèque QRCode avec fallbacks multiples
function loadQRCodeLibrary() {
    return new Promise((resolve, reject) => {
        // Si déjà chargée
        if (typeof QRCode !== 'undefined') {
            resolve();
            return;
        }
        
        console.log('QR Generator: Loading QRCode library...');
        
        // Essayer plusieurs CDN
        const cdnUrls = [
            'https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.3/qrcode.min.js',
            'https://unpkg.com/qrcode@1.5.3/build/qrcode.min.js'
        ];
        
        let cdnIndex = 0;
        
        function tryNextCDN() {
            if (cdnIndex >= cdnUrls.length) {
                // Tous les CDN ont échoué, utiliser le fallback API
                console.log('QR Generator: All CDNs failed, using API fallback');
                createQRCodeFallback();
                resolve();
                return;
            }
            
            const script = document.createElement('script');
            script.onload = () => {
                console.log(`QR Generator: CDN ${cdnIndex + 1} loaded successfully`);
                resolve();
            };
            script.onerror = () => {
                console.log(`QR Generator: CDN ${cdnIndex + 1} failed, trying next...`);
                cdnIndex++;
                tryNextCDN();
            };
            script.src = cdnUrls[cdnIndex];
            document.head.appendChild(script);
            
            // Timeout après 3 secondes
            setTimeout(() => {
                if (typeof QRCode === 'undefined') {
                    script.onerror();
                }
            }, 3000);
        }
        
        tryNextCDN();
    });
}

// Créer un fallback utilisant une API externe
function createQRCodeFallback() {
    console.log('QR Generator: Creating API fallback');
    
    window.QRCode = function(element, options) {
        if (typeof element === 'string') {
            element = document.getElementById(element);
        }
        
        const text = options.text || options;
        const size = options.width || options.height || 256;
        const margin = options.margin || 2;
        
        // API QR fiable et gratuite
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(text)}&margin=${margin}`;
        
        const img = document.createElement('img');
        img.src = qrUrl;
        img.width = size;
        img.height = size;
        img.alt = 'QR Code';
        img.style.display = 'block';
        img.style.margin = '0 auto';
        img.crossOrigin = 'anonymous'; // Pour permettre le canvas
        
        // Gérer les erreurs de chargement d'image
        img.onerror = () => {
            console.error('QR Generator: API QR Server failed');
            element.innerHTML = '<p style="color: red; text-align: center;">Erreur: Impossible de générer le QR Code</p>';
        };
        
        element.innerHTML = '';
        element.appendChild(img);
        
        return {
            makeCode: function(newText) {
                const newUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(newText)}&margin=${margin}`;
                img.src = newUrl;
            }
        };
    };
    
    // Niveaux de correction d'erreur (compatibilité)
    window.QRCode.CorrectLevel = {
        L: 1,
        M: 0, 
        Q: 3,
        H: 2
    };
}

// Notification helper (globale)
function showNotification(message, type = 'info') {
    if (window.miniServices && window.miniServices.showNotification) {
        window.miniServices.showNotification(message, type);
    } else {
        // Fallback simple avec alert pour les erreurs
        if (type === 'error') {
            alert('Erreur: ' + message);
        } else if (type === 'success') {
            console.log('✅ ' + message);
        } else {
            console.log(`${type.toUpperCase()}: ${message}`);
        }
    }
}

// Affichage d'erreur (globale)
function showError(message) {
    const resultSection = document.getElementById('resultSection');
    const resultContent = document.getElementById('resultContent');
    
    if (resultSection && resultContent) {
        const errorHTML = `
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <p>${message}</p>
            </div>
        `;
        
        resultContent.innerHTML = errorHTML;
        resultSection.style.display = 'block';
        resultSection.scrollIntoView({ behavior: 'smooth' });
    }
    
    showNotification(message, 'error');
}

// Validations (globales)
function isValidUrl(url) {
    try {
        new URL(url);
        return true;
    } catch {
        return false;
    }
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function initQRGenerator() {
    const serviceForm = document.getElementById('serviceForm');
    const resultSection = document.getElementById('resultSection');
    const resultContent = document.getElementById('resultContent');
    const submitBtn = document.getElementById('submitBtn');
    
    // Gestion de la soumission du formulaire - avec priorité haute
    if (serviceForm) {
        serviceForm.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation(); // Empêcher tous les autres handlers
            generateQRCode();
        }, true); // Utiliser capture pour priorité élevée
    }

    // Génération du QR Code
    function generateQRCode() {
        const formData = new FormData(serviceForm);
        const qrType = formData.get('qr_type');
        
        // Afficher le loading
        if (window.miniServices && window.miniServices.showButtonLoading) {
            window.miniServices.showButtonLoading(submitBtn);
        } else {
            // Fallback simple
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération...';
            }
        }
        
        // Construire le contenu selon le type
        let content = '';
        let isValid = false;
        
        try {
            switch (qrType) {
                case 'url':
                    content = formData.get('qr_url');
                    isValid = isValidUrl(content);
                    break;
                    
                case 'text':
                    content = formData.get('qr_text');
                    isValid = content && content.trim().length > 0;
                    break;
                    
                case 'email':
                    const email = formData.get('qr_email');
                    const subject = formData.get('qr_email_subject') || '';
                    const body = formData.get('qr_email_body') || '';
                    
                    content = `mailto:${email}`;
                    if (subject || body) {
                        const params = new URLSearchParams();
                        if (subject) params.append('subject', subject);
                        if (body) params.append('body', body);
                        content += '?' + params.toString();
                    }
                    isValid = isValidEmail(email);
                    break;
                    
                case 'phone':
                    const phone = formData.get('qr_phone');
                    content = `tel:${phone}`;
                    isValid = phone && phone.trim().length > 0;
                    break;
                    
                case 'wifi':
                    const ssid = formData.get('qr_wifi_ssid');
                    const password = formData.get('qr_wifi_password') || '';
                    const security = formData.get('qr_wifi_security') || 'WPA';
                    const hidden = formData.get('qr_wifi_hidden') ? 'true' : 'false';
                    
                    content = `WIFI:T:${security};S:${ssid};P:${password};H:${hidden};;`;
                    isValid = ssid && ssid.trim().length > 0;
                    break;
                    
                case 'vcard':
                    const name = formData.get('qr_vcard_name');
                    const company = formData.get('qr_vcard_company') || '';
                    const title = formData.get('qr_vcard_title') || '';
                    const vcardPhone = formData.get('qr_vcard_phone') || '';
                    const vcardEmail = formData.get('qr_vcard_email') || '';
                    const website = formData.get('qr_vcard_website') || '';
                    const address = formData.get('qr_vcard_address') || '';
                    
                    content = [
                        'BEGIN:VCARD',
                        'VERSION:3.0',
                        `FN:${name}`,
                        company ? `ORG:${company}` : '',
                        title ? `TITLE:${title}` : '',
                        vcardPhone ? `TEL:${vcardPhone}` : '',
                        vcardEmail ? `EMAIL:${vcardEmail}` : '',
                        website ? `URL:${website}` : '',
                        address ? `ADR:;;${address};;;` : '',
                        'END:VCARD'
                    ].filter(line => line).join('\n');
                    
                    isValid = name && name.trim().length > 0;
                    break;
                    
                default:
                    throw new Error('Type de QR Code non supporté');
            }
            
            if (!isValid) {
                throw new Error('Veuillez remplir les champs obligatoires');
            }
            
            // Générer le QR Code
            setTimeout(() => {
                renderQRCode(content, formData);
            }, 500);
            
        } catch (error) {
            console.error('Erreur lors de la génération:', error);
            showError(error.message);
            
            if (window.miniServices && window.miniServices.hideButtonLoading) {
                window.miniServices.hideButtonLoading(submitBtn);
            } else {
                // Fallback simple
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-qrcode"></i> Générer le QR Code';
                }
            }
        }
    }

    // Rendu du QR Code
    function renderQRCode(content, formData) {
        const size = parseInt(formData.get('qr_size')) || 300;
        const format = formData.get('qr_format') || 'png';
        const errorLevel = formData.get('qr_error_level') || 'M';
        const margin = parseInt(formData.get('qr_margin')) || 2;
        
        // Couleurs pour utilisateurs premium
        const colorFg = formData.get('qr_color_fg') || '#000000';
        const colorBg = formData.get('qr_color_bg') || '#FFFFFF';
        
        // Créer le conteneur pour le QR code
        const qrContainer = document.createElement('div');
        qrContainer.id = 'qr-code-container';
        qrContainer.style.textAlign = 'center';
        
        try {
            // Configuration du QR Code
            const qrOptions = {
                text: content,
                width: size,
                height: size,
                colorDark: colorFg,
                colorLight: colorBg,
                correctLevel: QRCode.CorrectLevel[errorLevel] || QRCode.CorrectLevel.M,
                margin: margin
            };
            
            // Générer selon le format
            if (format === 'svg') {
                // Pour SVG, utiliser une approche différente
                const qrCode = new QRCode(qrContainer, {
                    ...qrOptions,
                    useSVG: true
                });
            } else {
                // Pour PNG/JPG
                const qrCode = new QRCode(qrContainer, qrOptions);
            }
            
            // Afficher le résultat
            showResult(qrContainer, content, format, size);
            
        } catch (error) {
            console.error('Erreur de rendu QR:', error);
            showError('Erreur lors de la génération du QR Code');
        }
        
        if (window.miniServices && window.miniServices.hideButtonLoading) {
            window.miniServices.hideButtonLoading(submitBtn);
        } else {
            // Fallback simple
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-qrcode"></i> Générer le QR Code';
            }
        }
    }

    // Affichage du résultat
    function showResult(qrContainer, content, format, size) {
        const downloadBtn = document.getElementById('downloadBtn');
        const copyBtn = document.getElementById('copyBtn');
        
        // Construire le HTML du résultat
        const resultHTML = `
            <div class="qr-result">
                <div class="qr-display">
                    ${qrContainer.outerHTML}
                </div>
                <div class="qr-info">
                    <p><strong>Type :</strong> ${document.getElementById('qr-type').value.toUpperCase()}</p>
                    <p><strong>Taille :</strong> ${size}×${size}px</p>
                    <p><strong>Format :</strong> ${format.toUpperCase()}</p>
                    <p><strong>Contenu :</strong> <code>${content.length > 50 ? content.substring(0, 50) + '...' : content}</code></p>
                </div>
            </div>
        `;
        
        resultContent.innerHTML = resultHTML;
        resultSection.style.display = 'block';
        
        // Montrer les boutons d'action
        if (downloadBtn) {
            downloadBtn.style.display = 'inline-block';
            downloadBtn.onclick = () => downloadQRCode(format);
        }
        
        if (copyBtn) {
            copyBtn.style.display = 'inline-block';
            copyBtn.onclick = () => copyQRCode();
        }
        
        // Scroll vers le résultat
        resultSection.scrollIntoView({ behavior: 'smooth' });
        
        // Tracking
        if (window.miniServices && window.miniServices.trackUserInteraction) {
            window.miniServices.trackUserInteraction('qr_generated', document.getElementById('qr-type').value);
        }
        
        // Notification de succès
        showNotification('QR Code généré avec succès!', 'success');
    }

    // Téléchargement du QR Code
    function downloadQRCode(format) {
        const qrImg = document.querySelector('#qr-code-container img');
        const qrSvg = document.querySelector('#qr-code-container svg');
        
        if (format === 'svg' && qrSvg) {
            // Télécharger SVG
            const svgData = new XMLSerializer().serializeToString(qrSvg);
            const svgBlob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(svgBlob);
            
            const link = document.createElement('a');
            link.href = url;
            link.download = `qrcode.${format}`;
            link.click();
            
            URL.revokeObjectURL(url);
        } else if (qrImg) {
            // Télécharger PNG/JPG
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            canvas.width = qrImg.width;
            canvas.height = qrImg.height;
            
            ctx.drawImage(qrImg, 0, 0);
            
            canvas.toBlob((blob) => {
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `qrcode.${format}`;
                link.click();
                URL.revokeObjectURL(url);
            }, `image/${format === 'jpg' ? 'jpeg' : format}`);
        }
        
        // Tracking du téléchargement
        if (window.miniServices && window.miniServices.trackUserInteraction) {
            window.miniServices.trackUserInteraction('qr_downloaded', format);
        }
    }

    // Copie du QR Code
    function copyQRCode() {
        const qrImg = document.querySelector('#qr-code-container img');
        
        if (qrImg) {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            canvas.width = qrImg.width;
            canvas.height = qrImg.height;
            ctx.drawImage(qrImg, 0, 0);
            
            canvas.toBlob((blob) => {
                const item = new ClipboardItem({ 'image/png': blob });
                navigator.clipboard.write([item]).then(() => {
                    showNotification('QR Code copié dans le presse-papier!', 'success');
                }).catch(() => {
                    showNotification('Erreur lors de la copie', 'error');
                });
            });
        }
    }
}

// Styles additionnels pour les résultats QR
const qrStyles = `
<style>
.qr-result {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
}

.qr-display {
    padding: 1rem;
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
}

.qr-display img,
.qr-display svg {
    display: block;
    max-width: 100%;
    height: auto;
}

.qr-info {
    text-align: left;
    width: 100%;
    max-width: 400px;
    padding: 1rem;
    background: var(--background-light);
    border-radius: var(--radius);
    font-size: 0.9rem;
}

.qr-info p {
    margin: 0.5rem 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.qr-info code {
    background: var(--background-white);
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius-sm);
    font-family: 'Courier New', monospace;
    font-size: 0.8rem;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.error-message {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding: 2rem;
    color: var(--error-color);
    text-align: center;
}

.error-message i {
    font-size: 3rem;
    opacity: 0.7;
}

@media (max-width: 768px) {
    .qr-result {
        gap: 1rem;
    }
    
    .qr-info {
        font-size: 0.8rem;
    }
    
    .qr-info p {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
    
    .qr-info code {
        max-width: 100%;
    }
}
</style>
`;

// Injecter les styles
document.head.insertAdjacentHTML('beforeend', qrStyles);
