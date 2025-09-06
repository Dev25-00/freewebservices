/**
 * JavaScript pour les améliorations mobiles - Phase 1
 * Gestion du menu hamburger, interactions tactiles et optimisations mobile
 */

class MobileMenuManager {
    constructor() {
        this.hamburger = document.querySelector('.hamburger');
        this.navMenu = document.querySelector('.nav-menu');
        this.navLinks = document.querySelectorAll('.nav-link');
        this.body = document.body;
        this.overlay = null;
        this.isMenuOpen = false;
        this.touchStartY = 0;
        this.touchStartX = 0;
        
        this.init();
    }
    
    init() {
        this.createOverlay();
        this.bindEvents();
        this.handleResize();
        this.optimizeForMobile();
        
        // Écouter les changements d'orientation
        window.addEventListener('orientationchange', () => {
            setTimeout(() => this.handleResize(), 100);
        });
    }
    
    createOverlay() {
        this.overlay = document.createElement('div');
        this.overlay.className = 'mobile-menu-overlay';
        this.body.appendChild(this.overlay);
    }
    
    bindEvents() {
        if (this.hamburger) {
            this.hamburger.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleMenu();
            });
        }
        
        if (this.overlay) {
            this.overlay.addEventListener('click', () => {
                this.closeMenu();
            });
        }
        
        // Fermer le menu lors du clic sur un lien
        this.navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (this.isMenuOpen) {
                    this.closeMenu();
                }
            });
        });
        
        // Gestion des gestes de balayage
        if (this.navMenu) {
            this.navMenu.addEventListener('touchstart', this.handleTouchStart.bind(this), { passive: true });
            this.navMenu.addEventListener('touchmove', this.handleTouchMove.bind(this), { passive: false });
            this.navMenu.addEventListener('touchend', this.handleTouchEnd.bind(this), { passive: true });
        }
        
        // Fermer avec la touche Échap
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isMenuOpen) {
                this.closeMenu();
            }
        });
        
        // Gestion du redimensionnement de la fenêtre
        window.addEventListener('resize', this.handleResize.bind(this));
    }
    
    toggleMenu() {
        if (this.isMenuOpen) {
            this.closeMenu();
        } else {
            this.openMenu();
        }
    }
    
    openMenu() {
        this.isMenuOpen = true;
        this.hamburger.classList.add('active');
        this.navMenu.classList.add('active');
        this.overlay.classList.add('active');
        this.body.classList.add('menu-open');
        
        // Empêcher le scroll du body
        this.body.style.overflow = 'hidden';
        
        // Focus sur le premier lien pour l'accessibilité
        setTimeout(() => {
            const firstLink = this.navMenu.querySelector('.nav-link');
            if (firstLink) {
                firstLink.focus();
            }
        }, 300);
        
        // Vibration légère si supportée
        if (navigator.vibrate) {
            navigator.vibrate(10);
        }
    }
    
    closeMenu() {
        this.isMenuOpen = false;
        this.hamburger.classList.remove('active');
        this.navMenu.classList.remove('active');
        this.overlay.classList.remove('active');
        this.body.classList.remove('menu-open');
        
        // Restaurer le scroll du body
        this.body.style.overflow = '';
        
        // Remettre le focus sur le hamburger
        this.hamburger.focus();
    }
    
    handleTouchStart(e) {
        this.touchStartY = e.touches[0].clientY;
        this.touchStartX = e.touches[0].clientX;
    }
    
    handleTouchMove(e) {
        if (!this.isMenuOpen) return;
        
        const touchY = e.touches[0].clientY;
        const touchX = e.touches[0].clientX;
        const deltaY = touchY - this.touchStartY;
        const deltaX = touchX - this.touchStartX;
        
        // Fermer si balayage vers la droite > 100px
        if (deltaX > 100 && Math.abs(deltaY) < 50) {
            e.preventDefault();
            this.closeMenu();
        }
    }
    
    handleTouchEnd(e) {
        this.touchStartY = 0;
        this.touchStartX = 0;
    }
    
    handleResize() {
        // Fermer le menu si on revient sur desktop
        if (window.innerWidth > 768 && this.isMenuOpen) {
            this.closeMenu();
        }
        
        // Ajuster la hauteur du menu en fonction du viewport
        if (this.navMenu && window.innerWidth <= 768) {
            const headerHeight = document.querySelector('.header')?.offsetHeight || 60;
            this.navMenu.style.top = headerHeight + 'px';
            this.navMenu.style.height = `calc(100vh - ${headerHeight}px)`;
        }
    }
    
    optimizeForMobile() {
        // Détecter si c'est un appareil tactile
        const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        
        if (isTouchDevice) {
            this.body.classList.add('touch-device');
            
            // Améliorer les interactions tactiles
            this.improveTouchInteractions();
        }
        
        // Optimisations spécifiques iOS
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        if (isIOS) {
            this.body.classList.add('ios-device');
            this.optimizeForIOS();
        }
        
        // Optimisations spécifiques Android
        const isAndroid = /Android/.test(navigator.userAgent);
        if (isAndroid) {
            this.body.classList.add('android-device');
        }
    }
    
    improveTouchInteractions() {
        // Améliorer le feedback tactile pour tous les boutons
        const touchElements = document.querySelectorAll('.btn, .service-card, .nav-link');
        
        touchElements.forEach(element => {
            element.addEventListener('touchstart', function() {
                this.classList.add('touch-active');
            }, { passive: true });
            
            element.addEventListener('touchend', function() {
                setTimeout(() => {
                    this.classList.remove('touch-active');
                }, 150);
            }, { passive: true });
            
            element.addEventListener('touchcancel', function() {
                this.classList.remove('touch-active');
            }, { passive: true });
        });
    }
    
    optimizeForIOS() {
        // Gérer la barre d'état iOS
        const metaViewport = document.querySelector('meta[name="viewport"]');
        if (metaViewport) {
            metaViewport.setAttribute('content', 
                'width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no'
            );
        }
        
        // Ajuster pour les encoches iPhone
        if (CSS.supports('padding-top: env(safe-area-inset-top)')) {
            document.documentElement.style.setProperty('--safe-area-top', 'env(safe-area-inset-top)');
            document.documentElement.style.setProperty('--safe-area-bottom', 'env(safe-area-inset-bottom)');
        }
    }
}

// Gestionnaire de notifications mobile
class MobileNotificationManager {
    constructor() {
        this.container = null;
        this.init();
    }
    
    init() {
        this.createContainer();
    }
    
    createContainer() {
        this.container = document.createElement('div');
        this.container.className = 'mobile-notifications-container';
        this.container.style.cssText = `
            position: fixed;
            top: 70px;
            left: 10px;
            right: 10px;
            z-index: 1002;
            pointer-events: none;
        `;
        document.body.appendChild(this.container);
    }
    
    show(message, type = 'info', duration = 3000) {
        const notification = document.createElement('div');
        notification.className = `mobile-notification mobile-notification-${type}`;
        notification.style.cssText = `
            background: ${this.getBackgroundColor(type)};
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: var(--shadow-lg);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            pointer-events: auto;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        `;
        
        const icon = this.getIcon(type);
        notification.innerHTML = `${icon} ${message}`;
        
        this.container.appendChild(notification);
        
        // Animation d'entrée
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        // Animation de sortie
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, duration);
        
        // Fermer au tap
        notification.addEventListener('click', () => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        });
    }
    
    getBackgroundColor(type) {
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#667eea'
        };
        return colors[type] || colors.info;
    }
    
    getIcon(type) {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        return icons[type] || icons.info;
    }
}

// Gestionnaire de chargement mobile
class MobileLoadingManager {
    constructor() {
        this.loadingElement = null;
        this.init();
    }
    
    init() {
        this.createLoadingElement();
    }
    
    createLoadingElement() {
        this.loadingElement = document.createElement('div');
        this.loadingElement.className = 'mobile-loading';
        this.loadingElement.innerHTML = `
            <div class="spinner"></div>
            <p>Traitement en cours...</p>
        `;
        document.body.appendChild(this.loadingElement);
    }
    
    show(message = 'Traitement en cours...') {
        const messageElement = this.loadingElement.querySelector('p');
        if (messageElement) {
            messageElement.textContent = message;
        }
        this.loadingElement.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    hide() {
        this.loadingElement.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Optimisations de performance mobile
class MobilePerformanceOptimizer {
    constructor() {
        this.init();
    }
    
    init() {
        this.optimizeImages();
        this.optimizeAnimations();
        this.handleSlowConnections();
        this.optimizeScrolling();
    }
    
    optimizeImages() {
        // Lazy loading des images
        const images = document.querySelectorAll('img[data-src]');
        
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            images.forEach(img => imageObserver.observe(img));
        }
    }
    
    optimizeAnimations() {
        // Désactiver les animations si l'utilisateur préfère moins de mouvement
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.style.setProperty('--mobile-menu-transition', '0s');
        }
    }
    
    handleSlowConnections() {
        // Détecter les connexions lentes
        if ('connection' in navigator) {
            const connection = navigator.connection;
            
            if (connection.effectiveType === 'slow-2g' || connection.effectiveType === '2g') {
                document.body.classList.add('slow-connection');
                
                // Réduire les animations
                document.documentElement.style.setProperty('--mobile-menu-transition', '0.1s');
                
                // Notification à l'utilisateur
                const notificationManager = new MobileNotificationManager();
                notificationManager.show(
                    'Connexion lente détectée. Interface simplifiée activée.',
                    'info',
                    5000
                );
            }
        }
    }
    
    optimizeScrolling() {
        // Optimiser le scroll sur mobile
        let ticking = false;
        
        function updateScrollPosition() {
            // Logique d'optimisation du scroll
            ticking = false;
        }
        
        function requestTick() {
            if (!ticking) {
                requestAnimationFrame(updateScrollPosition);
                ticking = true;
            }
        }
        
        window.addEventListener('scroll', requestTick, { passive: true });
    }
}

// Gestionnaire d'accessibilité mobile
class MobileAccessibilityManager {
    constructor() {
        this.init();
    }
    
    init() {
        this.improveKeyboardNavigation();
        this.addARIALabels();
        this.handleFocusManagement();
    }
    
    improveKeyboardNavigation() {
        // Améliorer la navigation au clavier sur mobile
        const focusableElements = document.querySelectorAll(
            'a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        focusableElements.forEach(element => {
            element.addEventListener('focus', function() {
                this.classList.add('keyboard-focus');
            });
            
            element.addEventListener('blur', function() {
                this.classList.remove('keyboard-focus');
            });
        });
    }
    
    addARIALabels() {
        // Ajouter des labels ARIA pour l'accessibilité
        const hamburger = document.querySelector('.hamburger');
        if (hamburger) {
            hamburger.setAttribute('aria-label', 'Menu de navigation');
            hamburger.setAttribute('aria-expanded', 'false');
            hamburger.setAttribute('aria-controls', 'navigation-menu');
        }
        
        const navMenu = document.querySelector('.nav-menu');
        if (navMenu) {
            navMenu.setAttribute('id', 'navigation-menu');
            navMenu.setAttribute('aria-hidden', 'true');
        }
    }
    
    handleFocusManagement() {
        // Gérer le focus lors de l'ouverture/fermeture du menu
        document.addEventListener('menuOpen', () => {
            const navMenu = document.querySelector('.nav-menu');
            if (navMenu) {
                navMenu.setAttribute('aria-hidden', 'false');
            }
            
            const hamburger = document.querySelector('.hamburger');
            if (hamburger) {
                hamburger.setAttribute('aria-expanded', 'true');
            }
        });
        
        document.addEventListener('menuClose', () => {
            const navMenu = document.querySelector('.nav-menu');
            if (navMenu) {
                navMenu.setAttribute('aria-hidden', 'true');
            }
            
            const hamburger = document.querySelector('.hamburger');
            if (hamburger) {
                hamburger.setAttribute('aria-expanded', 'false');
            }
        });
    }
}

// Gestionnaire de gestes tactiles avancés
class TouchGestureManager {
    constructor() {
        this.touchStartX = 0;
        this.touchStartY = 0;
        this.touchEndX = 0;
        this.touchEndY = 0;
        this.minSwipeDistance = 30;
        
        this.init();
    }
    
    init() {
        this.bindGlobalTouchEvents();
    }
    
    bindGlobalTouchEvents() {
        document.addEventListener('touchstart', this.handleTouchStart.bind(this), { passive: true });
        document.addEventListener('touchend', this.handleTouchEnd.bind(this), { passive: true });
    }
    
    handleTouchStart(e) {
        this.touchStartX = e.changedTouches[0].screenX;
        this.touchStartY = e.changedTouches[0].screenY;
    }
    
    handleTouchEnd(e) {
        this.touchEndX = e.changedTouches[0].screenX;
        this.touchEndY = e.changedTouches[0].screenY;
        
        this.handleSwipe();
    }
    
    handleSwipe() {
        const deltaX = this.touchEndX - this.touchStartX;
        const deltaY = this.touchEndY - this.touchStartY;
        
        if (Math.abs(deltaX) > Math.abs(deltaY)) {
            // Geste horizontal
            if (Math.abs(deltaX) > this.minSwipeDistance) {
                if (deltaX > 0) {
                    this.onSwipeRight();
                } else {
                    this.onSwipeLeft();
                }
            }
        } else {
            // Geste vertical
            if (Math.abs(deltaY) > this.minSwipeDistance) {
                if (deltaY > 0) {
                    this.onSwipeDown();
                } else {
                    this.onSwipeUp();
                }
            }
        }
    }
    
    onSwipeLeft() {
        // Ouvrir le menu si swipe vers la gauche depuis le bord droit
        if (this.touchStartX > window.innerWidth - 50) {
            const menuManager = window.mobileMenuManager;
            if (menuManager && !menuManager.isMenuOpen) {
                menuManager.openMenu();
            }
        }
    }
    
    onSwipeRight() {
        // Fermer le menu si swipe vers la droite
        const menuManager = window.mobileMenuManager;
        if (menuManager && menuManager.isMenuOpen) {
            menuManager.closeMenu();
        }
    }
    
    onSwipeUp() {
        // Masquer la barre d'adresse sur mobile (scroll vers le haut)
        window.scrollTo(0, 0);
    }
    
    onSwipeDown() {
        // Logique pour swipe vers le bas
    }
}

// Initialisation globale au chargement du DOM
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si on est sur mobile
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile || 'ontouchstart' in window) {
        // Initialiser les gestionnaires mobiles
        window.mobileMenuManager = new MobileMenuManager();
        window.mobileNotificationManager = new MobileNotificationManager();
        window.mobileLoadingManager = new MobileLoadingManager();
        window.mobilePerformanceOptimizer = new MobilePerformanceOptimizer();
        window.mobileAccessibilityManager = new MobileAccessibilityManager();
        window.touchGestureManager = new TouchGestureManager();
        
        // Ajouter la classe mobile au body
        document.body.classList.add('mobile-optimized');
        
        console.log('🚀 Optimisations mobiles activées');
    }
});

// Fonctions utilitaires globales pour l'usage externe
window.MobileUtils = {
    showNotification: function(message, type = 'info', duration = 3000) {
        if (window.mobileNotificationManager) {
            window.mobileNotificationManager.show(message, type, duration);
        }
    },
    
    showLoading: function(message = 'Traitement en cours...') {
        if (window.mobileLoadingManager) {
            window.mobileLoadingManager.show(message);
        }
    },
    
    hideLoading: function() {
        if (window.mobileLoadingManager) {
            window.mobileLoadingManager.hide();
        }
    },
    
    vibrate: function(pattern = 10) {
        if (navigator.vibrate) {
            navigator.vibrate(pattern);
        }
    },
    
    isTouch: function() {
        return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
    },
    
    isMobile: function() {
        return window.innerWidth <= 768;
    },
    
    getDeviceInfo: function() {
        return {
            isMobile: this.isMobile(),
            isTouch: this.isTouch(),
            isIOS: /iPad|iPhone|iPod/.test(navigator.userAgent),
            isAndroid: /Android/.test(navigator.userAgent),
            viewportWidth: window.innerWidth,
            viewportHeight: window.innerHeight,
            devicePixelRatio: window.devicePixelRatio || 1
        };
    }
};

// Event listeners pour les changements d'orientation et de taille
window.addEventListener('resize', function() {
    // Réinitialiser certaines optimisations lors du redimensionnement
    if (window.mobileMenuManager) {
        window.mobileMenuManager.handleResize();
    }
});

window.addEventListener('orientationchange', function() {
    // Gérer les changements d'orientation
    setTimeout(() => {
        if (window.mobileMenuManager) {
            window.mobileMenuManager.handleResize();
        }
        
        // Recalculer les hauteurs et positions
        const header = document.querySelector('.header');
        if (header) {
            const headerHeight = header.offsetHeight;
            document.documentElement.style.setProperty('--header-height', headerHeight + 'px');
        }
    }, 100);
});

// Gestion de la visibilité de la page (économie d'énergie)
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        // Page masquée - réduire les activités
        if (window.mobilePerformanceOptimizer) {
            // Pause des animations non critiques
        }
    } else {
        // Page visible - reprendre les activités normales
    }
});

// Export pour usage en module si nécessaire
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        MobileMenuManager,
        MobileNotificationManager,
        MobileLoadingManager,
        MobilePerformanceOptimizer,
        MobileAccessibilityManager,
        TouchGestureManager
    };
}
