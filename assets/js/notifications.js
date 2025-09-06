/**
 * Système de notifications avancé pour mini-services.tech
 * Gestion des notifications toast, alertes de sécurité et feedback utilisateur
 */

class NotificationSystem {
    constructor() {
        this.container = null;
        this.notifications = new Map();
        this.defaultDuration = 5000;
        this.maxNotifications = 5;
        this.init();
    }

    init() {
        this.createContainer();
        this.setupStyles();
        this.setupEventListeners();
    }

    createContainer() {
        this.container = document.createElement('div');
        this.container.id = 'notification-container';
        this.container.className = 'notification-container';
        document.body.appendChild(this.container);
    }

    setupStyles() {
        if (document.getElementById('notification-styles')) return;

        const styles = document.createElement('style');
        styles.id = 'notification-styles';
        styles.textContent = `
            .notification-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                max-width: 400px;
                pointer-events: none;
            }

            .notification {
                background: white;
                border-radius: 8px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.15);
                margin-bottom: 12px;
                padding: 16px 20px;
                border-left: 4px solid var(--primary-color);
                transform: translateX(100%);
                opacity: 0;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                pointer-events: auto;
                cursor: pointer;
                position: relative;
                overflow: hidden;
                max-width: 100%;
            }

            .notification.show {
                transform: translateX(0);
                opacity: 1;
            }

            .notification.success {
                border-left-color: var(--success-color);
            }

            .notification.error {
                border-left-color: var(--danger-color);
            }

            .notification.warning {
                border-left-color: var(--warning-color);
            }

            .notification.info {
                border-left-color: var(--primary-color);
            }

            .notification.security {
                border-left-color: #ff4757;
                background: linear-gradient(135deg, #fff5f5, #ffffff);
            }

            .notification-header {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 8px;
                font-weight: 600;
                font-size: 0.95rem;
            }

            .notification-icon {
                font-size: 1.2rem;
                flex-shrink: 0;
            }

            .notification.success .notification-icon {
                color: var(--success-color);
            }

            .notification.error .notification-icon {
                color: var(--danger-color);
            }

            .notification.warning .notification-icon {
                color: var(--warning-color);
            }

            .notification.info .notification-icon {
                color: var(--primary-color);
            }

            .notification.security .notification-icon {
                color: #ff4757;
                animation: pulse 1.5s infinite;
            }

            .notification-content {
                font-size: 0.9rem;
                line-height: 1.4;
                color: var(--text-color);
            }

            .notification-close {
                position: absolute;
                top: 8px;
                right: 8px;
                background: none;
                border: none;
                font-size: 1.2rem;
                color: var(--text-light);
                cursor: pointer;
                padding: 4px;
                border-radius: 4px;
                opacity: 0.7;
                transition: all 0.2s ease;
            }

            .notification-close:hover {
                opacity: 1;
                background: rgba(0,0,0,0.1);
            }

            .notification-progress {
                position: absolute;
                bottom: 0;
                left: 0;
                height: 3px;
                background: currentColor;
                opacity: 0.3;
                transition: width linear;
            }

            .notification.success .notification-progress {
                background: var(--success-color);
            }

            .notification.error .notification-progress {
                background: var(--danger-color);
            }

            .notification.warning .notification-progress {
                background: var(--warning-color);
            }

            .notification.info .notification-progress {
                background: var(--primary-color);
            }

            .notification-actions {
                margin-top: 12px;
                display: flex;
                gap: 8px;
            }

            .notification-action {
                padding: 6px 12px;
                border: 1px solid var(--border-color);
                background: white;
                border-radius: 4px;
                font-size: 0.8rem;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .notification-action:hover {
                background: var(--light-color);
            }

            .notification-action.primary {
                background: var(--primary-color);
                color: white;
                border-color: var(--primary-color);
            }

            .notification-action.primary:hover {
                background: var(--primary-dark);
            }

            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }

            @media (max-width: 768px) {
                .notification-container {
                    top: 10px;
                    right: 10px;
                    left: 10px;
                    max-width: none;
                }

                .notification {
                    margin-bottom: 8px;
                    padding: 12px 16px;
                }
            }
        `;
        document.head.appendChild(styles);
    }

    setupEventListeners() {
        // Écouter les événements globaux de sécurité
        document.addEventListener('securityAlert', (event) => {
            this.showSecurity(event.detail.message, event.detail.details);
        });

        // Écouter les erreurs de validation CSRF
        document.addEventListener('csrfError', (event) => {
            this.showSecurity('Erreur de sécurité CSRF détectée', {
                actions: [{
                    label: 'Actualiser la page',
                    action: 'reload',
                    primary: true
                }]
            });
        });

        // Écouter les erreurs de rate limiting
        document.addEventListener('rateLimitError', (event) => {
            this.showWarning(`Limite de requêtes atteinte. Réessayez dans ${event.detail.retryAfter} secondes.`);
        });
    }

    show(message, type = 'info', options = {}) {
        const id = this.generateId();
        const duration = options.duration || this.defaultDuration;
        
        // Limiter le nombre de notifications
        if (this.notifications.size >= this.maxNotifications) {
            const oldestId = this.notifications.keys().next().value;
            this.hide(oldestId);
        }

        const notification = this.createNotification(id, message, type, options);
        this.container.appendChild(notification);
        this.notifications.set(id, {
            element: notification,
            timer: null
        });

        // Animation d'entrée
        requestAnimationFrame(() => {
            notification.classList.add('show');
        });

        // Programmation de la disparition automatique
        if (duration > 0) {
            this.setAutoHide(id, duration);
        }

        return id;
    }

    showSuccess(message, options = {}) {
        return this.show(message, 'success', options);
    }

    showError(message, options = {}) {
        return this.show(message, 'error', options);
    }

    showWarning(message, options = {}) {
        return this.show(message, 'warning', options);
    }

    showInfo(message, options = {}) {
        return this.show(message, 'info', options);
    }

    showSecurity(message, options = {}) {
        return this.show(message, 'security', {
            duration: 0, // Pas de disparition automatique pour les alertes de sécurité
            ...options
        });
    }

    createNotification(id, message, type, options) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.dataset.id = id;

        const icon = this.getIcon(type);
        const title = options.title || this.getTitle(type);

        notification.innerHTML = `
            <div class="notification-header">
                <i class="notification-icon ${icon}"></i>
                <span>${title}</span>
            </div>
            <div class="notification-content">${message}</div>
            <button class="notification-close">&times;</button>
            ${options.actions ? this.createActions(options.actions) : ''}
            ${options.duration > 0 ? '<div class="notification-progress"></div>' : ''}
        `;

        // Gestionnaire de fermeture
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => this.hide(id));

        // Click sur la notification pour fermer
        notification.addEventListener('click', (e) => {
            if (!e.target.matches('.notification-action, .notification-action *')) {
                this.hide(id);
            }
        });

        // Gestionnaires d'actions
        if (options.actions) {
            const actionBtns = notification.querySelectorAll('.notification-action');
            actionBtns.forEach((btn, index) => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const action = options.actions[index];
                    if (action.callback) {
                        action.callback();
                    }
                    if (action.action === 'reload') {
                        window.location.reload();
                    }
                    this.hide(id);
                });
            });
        }

        return notification;
    }

    createActions(actions) {
        if (!Array.isArray(actions)) return '';

        const actionsHtml = actions.map(action => 
            `<button class="notification-action ${action.primary ? 'primary' : ''}" 
                     data-action="${action.action}">${action.label}</button>`
        ).join('');

        return `<div class="notification-actions">${actionsHtml}</div>`;
    }

    getIcon(type) {
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle',
            security: 'fas fa-shield-alt'
        };
        return icons[type] || icons.info;
    }

    getTitle(type) {
        const titles = {
            success: 'Succès',
            error: 'Erreur',
            warning: 'Attention',
            info: 'Information',
            security: 'Alerte de Sécurité'
        };
        return titles[type] || titles.info;
    }

    setAutoHide(id, duration) {
        const notificationData = this.notifications.get(id);
        if (!notificationData) return;

        const progressBar = notificationData.element.querySelector('.notification-progress');
        if (progressBar) {
            progressBar.style.width = '100%';
            progressBar.style.transitionDuration = duration + 'ms';
            requestAnimationFrame(() => {
                progressBar.style.width = '0%';
            });
        }

        notificationData.timer = setTimeout(() => {
            this.hide(id);
        }, duration);
    }

    hide(id) {
        const notificationData = this.notifications.get(id);
        if (!notificationData) return;

        if (notificationData.timer) {
            clearTimeout(notificationData.timer);
        }

        notificationData.element.classList.remove('show');
        
        setTimeout(() => {
            if (notificationData.element.parentNode) {
                notificationData.element.parentNode.removeChild(notificationData.element);
            }
            this.notifications.delete(id);
        }, 300);
    }

    hideAll() {
        this.notifications.forEach((_, id) => {
            this.hide(id);
        });
    }

    generateId() {
        return 'notification_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    // Méthodes utilitaires pour les différents types d'alertes
    showUploadProgress(filename, progress) {
        return this.show(`Upload de ${filename} en cours...`, 'info', {
            title: 'Upload',
            duration: 0,
            actions: [{
                label: 'Annuler',
                action: 'cancel',
                callback: () => {
                    // Logique d'annulation d'upload
                    console.log('Upload annulé');
                }
            }]
        });
    }

    showConversionComplete(filename, downloadUrl) {
        return this.show(`Conversion de ${filename} terminée avec succès!`, 'success', {
            actions: [{
                label: 'Télécharger',
                action: 'download',
                primary: true,
                callback: () => {
                    window.open(downloadUrl, '_blank');
                }
            }]
        });
    }

    showFileValidationError(errors) {
        const errorList = Array.isArray(errors) ? 
            errors.map(error => `• ${error}`).join('<br>') : 
            errors;
        
        return this.show(`Fichier non valide:<br>${errorList}`, 'error', {
            title: 'Validation échouée',
            duration: 8000
        });
    }

    showSecurityThreat(threat, details = {}) {
        return this.show(
            `Menace de sécurité détectée: ${threat}`, 
            'security', 
            {
                title: 'Alerte de Sécurité',
                actions: [{
                    label: 'Plus d\'infos',
                    action: 'details',
                    callback: () => {
                        console.log('Détails de la menace:', details);
                    }
                }, {
                    label: 'Signaler',
                    action: 'report',
                    primary: true,
                    callback: () => {
                        // Logique de signalement
                        console.log('Menace signalée');
                    }
                }]
            }
        );
    }
}

// Système de progression avancé pour les conversions
class ProgressIndicator {
    constructor() {
        this.activeProgresses = new Map();
        this.setupStyles();
    }

    setupStyles() {
        if (document.getElementById('progress-styles')) return;

        const styles = document.createElement('style');
        styles.id = 'progress-styles';
        styles.textContent = `
            .progress-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                backdrop-filter: blur(2px);
            }

            .progress-modal {
                background: white;
                border-radius: 12px;
                padding: 30px;
                max-width: 400px;
                width: 90%;
                box-shadow: 0 20px 25px rgba(0,0,0,0.2);
                text-align: center;
            }

            .progress-icon {
                width: 60px;
                height: 60px;
                margin: 0 auto 20px;
                border: 3px solid var(--border-color);
                border-top: 3px solid var(--primary-color);
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }

            .progress-title {
                font-size: 1.2rem;
                font-weight: 600;
                color: var(--dark-color);
                margin-bottom: 10px;
            }

            .progress-description {
                color: var(--text-light);
                margin-bottom: 20px;
                font-size: 0.9rem;
            }

            .progress-bar-container {
                width: 100%;
                height: 8px;
                background: var(--border-color);
                border-radius: 4px;
                overflow: hidden;
                margin-bottom: 15px;
            }

            .progress-bar {
                height: 100%;
                background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
                width: 0%;
                transition: width 0.3s ease;
                border-radius: 4px;
            }

            .progress-percentage {
                font-weight: 600;
                color: var(--primary-color);
                font-size: 0.9rem;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(styles);
    }

    show(id, title, description = '') {
        const overlay = document.createElement('div');
        overlay.className = 'progress-overlay';
        overlay.innerHTML = `
            <div class="progress-modal">
                <div class="progress-icon"></div>
                <div class="progress-title">${title}</div>
                <div class="progress-description">${description}</div>
                <div class="progress-bar-container">
                    <div class="progress-bar"></div>
                </div>
                <div class="progress-percentage">0%</div>
            </div>
        `;

        document.body.appendChild(overlay);
        this.activeProgresses.set(id, {
            element: overlay,
            progressBar: overlay.querySelector('.progress-bar'),
            percentage: overlay.querySelector('.progress-percentage')
        });

        return id;
    }

    update(id, progress, description = null) {
        const progressData = this.activeProgresses.get(id);
        if (!progressData) return;

        const percentage = Math.min(100, Math.max(0, progress));
        progressData.progressBar.style.width = percentage + '%';
        progressData.percentage.textContent = Math.round(percentage) + '%';

        if (description) {
            const descElement = progressData.element.querySelector('.progress-description');
            descElement.textContent = description;
        }
    }

    hide(id) {
        const progressData = this.activeProgresses.get(id);
        if (!progressData) return;

        progressData.element.style.opacity = '0';
        progressData.element.style.transform = 'scale(0.9)';
        
        setTimeout(() => {
            if (progressData.element.parentNode) {
                progressData.element.parentNode.removeChild(progressData.element);
            }
            this.activeProgresses.delete(id);
        }, 300);
    }
}

// Initialisation globale
let notificationSystem;
let progressIndicator;

document.addEventListener('DOMContentLoaded', function() {
    notificationSystem = new NotificationSystem();
    progressIndicator = new ProgressIndicator();
    
    // Exposer globalement pour faciliter l'usage
    window.showNotification = (message, type, options) => notificationSystem.show(message, type, options);
    window.showSuccess = (message, options) => notificationSystem.showSuccess(message, options);
    window.showError = (message, options) => notificationSystem.showError(message, options);
    window.showWarning = (message, options) => notificationSystem.showWarning(message, options);
    window.showInfo = (message, options) => notificationSystem.showInfo(message, options);
    window.showProgress = (id, title, description) => progressIndicator.show(id, title, description);
    window.updateProgress = (id, progress, description) => progressIndicator.update(id, progress, description);
    window.hideProgress = (id) => progressIndicator.hide(id);
});

// Intercepteur AJAX global pour les erreurs de sécurité
(function() {
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        return originalFetch.apply(this, args)
            .then(response => {
                if (response.status === 403) {
                    response.json().then(data => {
                        if (data.code === 'CSRF_INVALID') {
                            document.dispatchEvent(new CustomEvent('csrfError', {
                                detail: data
                            }));
                        } else if (data.code === 'RATE_LIMIT_EXCEEDED') {
                            document.dispatchEvent(new CustomEvent('rateLimitError', {
                                detail: data
                            }));
                        }
                    }).catch(() => {});
                }
                return response;
            });
    };
})();