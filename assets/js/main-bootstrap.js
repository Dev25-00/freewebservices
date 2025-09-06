// assets/js/main-bootstrap.js
// JavaScript principal pour la version Bootstrap

document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialisation des popovers Bootstrap
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Animation d'apparition pour les éléments
    initScrollAnimations();
    
    // Gestion des formulaires
    initFormValidation();
    
    // Gestion des notifications
    initNotifications();
    
    // Smooth scrolling pour les ancres
    initSmoothScrolling();
});

// Animations au scroll
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                entry.target.classList.add('animate__fadeInUp');
            }
        });
    }, observerOptions);
    
    // Observer les éléments avec la classe 'animate-on-scroll'
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
}

// Validation des formulaires
function initFormValidation() {
    // Validation Bootstrap
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Validation en temps réel
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
        });
    });
}

function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    const required = field.hasAttribute('required');
    
    // Reset classes
    field.classList.remove('is-valid', 'is-invalid');
    
    // Validation selon le type
    let isValid = true;
    
    if (required && !value) {
        isValid = false;
    } else if (value) {
        switch (type) {
            case 'email':
                isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
                break;
            case 'password':
                isValid = value.length >= 6;
                break;
            case 'url':
                isValid = /^https?:\/\/.+/.test(value);
                break;
            case 'tel':
                isValid = /^[\d\s\+\-\(\)]+$/.test(value);
                break;
        }
    }
    
    // Appliquer les classes Bootstrap
    if (isValid) {
        field.classList.add('is-valid');
    } else {
        field.classList.add('is-invalid');
    }
    
    return isValid;
}

// Système de notifications
function initNotifications() {
    // Auto-hide alerts après 5 secondes
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            fadeOut(alert);
        }, 5000);
    });
}

function showNotification(message, type = 'info', duration = 5000) {
    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertContainer.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px;';
    
    alertContainer.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="${getIconForType(type)} me-2"></i>
            <span>${message}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(alertContainer);
    
    // Auto remove
    setTimeout(() => {
        if (alertContainer.parentNode) {
            fadeOut(alertContainer);
        }
    }, duration);
}

function getIconForType(type) {
    const icons = {
        'success': 'bi bi-check-circle-fill',
        'danger': 'bi bi-exclamation-triangle-fill',
        'warning': 'bi bi-exclamation-triangle-fill',
        'info': 'bi bi-info-circle-fill'
    };
    return icons[type] || icons.info;
}

function fadeOut(element) {
    element.style.transition = 'opacity 0.3s ease';
    element.style.opacity = '0';
    setTimeout(() => {
        if (element.parentNode) {
            element.parentNode.removeChild(element);
        }
    }, 300);
}

// Smooth scrolling
function initSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                const offsetTop = targetElement.offsetTop - 80; // Account for fixed navbar
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// Loading states pour les boutons
function showButtonLoading(button, text = 'Chargement...') {
    if (!button) return;
    
    button.disabled = true;
    button.dataset.originalText = button.innerHTML;
    button.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
        ${text}
    `;
}

function hideButtonLoading(button) {
    if (!button) return;
    
    button.disabled = false;
    button.innerHTML = button.dataset.originalText || button.innerHTML;
}

// Utilitaires pour les services
function copyToClipboard(text, successMessage = 'Copié !') {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification(successMessage, 'success');
        }).catch(() => {
            fallbackCopyTextToClipboard(text, successMessage);
        });
    } else {
        fallbackCopyTextToClipboard(text, successMessage);
    }
}

function fallbackCopyTextToClipboard(text, successMessage) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.top = '-9999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showNotification(successMessage, 'success');
    } catch (err) {
        showNotification('Impossible de copier automatiquement', 'warning');
    }
    
    document.body.removeChild(textArea);
}

// Export des fonctions principales pour utilisation globale
window.miniServices = {
    showNotification,
    showButtonLoading,
    hideButtonLoading,
    copyToClipboard,
    validateField
};