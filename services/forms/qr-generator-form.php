<!-- services/forms/qr-generator-form.php -->
<!-- Formulaire spécifique pour le générateur de QR Code -->

<div class="qr-type-selector">
    <h3>Type de QR Code</h3>
    <div class="type-options">
        <div class="type-option active" data-type="url">
            <i class="fas fa-link"></i>
            <span>URL/Lien</span>
        </div>
        <div class="type-option" data-type="text">
            <i class="fas fa-font"></i>
            <span>Texte</span>
        </div>
        <div class="type-option" data-type="email">
            <i class="fas fa-envelope"></i>
            <span>Email</span>
        </div>
        <div class="type-option" data-type="phone">
            <i class="fas fa-phone"></i>
            <span>Téléphone</span>
        </div>
        <div class="type-option" data-type="wifi">
            <i class="fas fa-wifi"></i>
            <span>WiFi</span>
        </div>
        <div class="type-option" data-type="vcard">
            <i class="fas fa-user"></i>
            <span>Contact</span>
        </div>
    </div>
</div>

<div class="qr-input-forms">
    <!-- Formulaire URL -->
    <div class="input-form active" id="url-form">
        <div class="form-group">
            <label for="url-input">
                <i class="fas fa-link"></i>
                URL du site web
            </label>
            <input type="url" id="url-input" name="qr_url" class="form-control" 
                   placeholder="https://example.com" required>
            <small class="form-help">Entrez l'adresse complète avec http:// ou https://</small>
        </div>
    </div>

    <!-- Formulaire Texte -->
    <div class="input-form" id="text-form">
        <div class="form-group">
            <label for="text-input">
                <i class="fas fa-font"></i>
                Votre texte
            </label>
            <textarea id="text-input" name="qr_text" class="form-control" rows="4" 
                      placeholder="Entrez votre texte ici..." maxlength="1000"></textarea>
            <small class="form-help">Maximum 1000 caractères</small>
        </div>
    </div>

    <!-- Formulaire Email -->
    <div class="input-form" id="email-form">
        <div class="form-group">
            <label for="email-input">
                <i class="fas fa-envelope"></i>
                Adresse email
            </label>
            <input type="email" id="email-input" name="qr_email" class="form-control" 
                   placeholder="contact@example.com" required>
        </div>
        <div class="form-group">
            <label for="email-subject">
                <i class="fas fa-tag"></i>
                Sujet (optionnel)
            </label>
            <input type="text" id="email-subject" name="qr_email_subject" class="form-control" 
                   placeholder="Sujet de l'email" maxlength="100">
        </div>
        <div class="form-group">
            <label for="email-body">
                <i class="fas fa-comment"></i>
                Message (optionnel)
            </label>
            <textarea id="email-body" name="qr_email_body" class="form-control" rows="3" 
                      placeholder="Corps du message..." maxlength="500"></textarea>
        </div>
    </div>

    <!-- Formulaire Téléphone -->
    <div class="input-form" id="phone-form">
        <div class="form-group">
            <label for="phone-input">
                <i class="fas fa-phone"></i>
                Numéro de téléphone
            </label>
            <input type="tel" id="phone-input" name="qr_phone" class="form-control" 
                   placeholder="+33 1 23 45 67 89" required>
            <small class="form-help">Format international recommandé (+33, +1, etc.)</small>
        </div>
    </div>

    <!-- Formulaire WiFi -->
    <div class="input-form" id="wifi-form">
        <div class="form-group">
            <label for="wifi-ssid">
                <i class="fas fa-wifi"></i>
                Nom du réseau (SSID)
            </label>
            <input type="text" id="wifi-ssid" name="qr_wifi_ssid" class="form-control" 
                   placeholder="Mon WiFi" required maxlength="50">
        </div>
        <div class="form-group">
            <label for="wifi-password">
                <i class="fas fa-key"></i>
                Mot de passe
            </label>
            <input type="text" id="wifi-password" name="qr_wifi_password" class="form-control" 
                   placeholder="motdepasse123" maxlength="50">
            <small class="form-help">Laissez vide pour un réseau ouvert</small>
        </div>
        <div class="form-group">
            <label for="wifi-security">
                <i class="fas fa-shield-alt"></i>
                Type de sécurité
            </label>
            <select id="wifi-security" name="qr_wifi_security" class="form-control">
                <option value="WPA">WPA/WPA2</option>
                <option value="WEP">WEP</option>
                <option value="nopass">Aucune sécurité</option>
            </select>
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" id="wifi-hidden" name="qr_wifi_hidden" value="1">
                Réseau masqué (SSID caché)
            </label>
        </div>
    </div>

    <!-- Formulaire Contact (vCard) -->
    <div class="input-form" id="vcard-form">
        <div class="form-group">
            <label for="vcard-name">
                <i class="fas fa-user"></i>
                Nom complet
            </label>
            <input type="text" id="vcard-name" name="qr_vcard_name" class="form-control" 
                   placeholder="Jean Dupont" required maxlength="100">
        </div>
        <div class="form-group">
            <label for="vcard-company">
                <i class="fas fa-building"></i>
                Entreprise
            </label>
            <input type="text" id="vcard-company" name="qr_vcard_company" class="form-control" 
                   placeholder="Ma Société" maxlength="100">
        </div>
        <div class="form-group">
            <label for="vcard-title">
                <i class="fas fa-briefcase"></i>
                Titre/Poste
            </label>
            <input type="text" id="vcard-title" name="qr_vcard_title" class="form-control" 
                   placeholder="Directeur commercial" maxlength="100">
        </div>
        <div class="form-group">
            <label for="vcard-phone">
                <i class="fas fa-phone"></i>
                Téléphone
            </label>
            <input type="tel" id="vcard-phone" name="qr_vcard_phone" class="form-control" 
                   placeholder="+33 1 23 45 67 89" maxlength="20">
        </div>
        <div class="form-group">
            <label for="vcard-email">
                <i class="fas fa-envelope"></i>
                Email
            </label>
            <input type="email" id="vcard-email" name="qr_vcard_email" class="form-control" 
                   placeholder="jean@example.com" maxlength="100">
        </div>
        <div class="form-group">
            <label for="vcard-website">
                <i class="fas fa-globe"></i>
                Site web
            </label>
            <input type="url" id="vcard-website" name="qr_vcard_website" class="form-control" 
                   placeholder="https://www.example.com" maxlength="200">
        </div>
        <div class="form-group">
            <label for="vcard-address">
                <i class="fas fa-map-marker-alt"></i>
                Adresse
            </label>
            <textarea id="vcard-address" name="qr_vcard_address" class="form-control" rows="2" 
                      placeholder="123 Rue de la Paix, 75001 Paris" maxlength="200"></textarea>
        </div>
    </div>
</div>

<div class="qr-options">
    <h3><i class="fas fa-cog"></i> Options de personnalisation</h3>
    <div class="options-grid">
        <div class="option-group">
            <label for="qr-size">
                <i class="fas fa-expand-arrows-alt"></i>
                Taille
            </label>
            <select id="qr-size" name="qr_size" class="form-control">
                <option value="200">200x200 px (Petit - Réseaux sociaux)</option>
                <option value="300" selected>300x300 px (Standard - Recommandé)</option>
                <option value="400">400x400 px (Grand - Affichage)</option>
                <option value="500">500x500 px (Très grand - Impression A4)</option>
                <option value="800">800x800 px (Impression haute qualité)</option>
            </select>
            <small class="option-help">Plus c'est grand, plus c'est facile à scanner de loin</small>
        </div>
        
        <div class="option-group">
            <label for="qr-format">
                <i class="fas fa-file-image"></i>
                Format d'export
            </label>
            <select id="qr-format" name="qr_format" class="form-control">
                <option value="png" selected>PNG (Recommandé - Haute qualité + transparence)</option>
                <option value="jpg">JPG (Plus petit - Pour email/web)</option>
                <option value="svg">SVG (Vectoriel - Redimensionnable à l'infini)</option>
            </select>
            <small class="option-help">PNG = qualité, JPG = taille, SVG = vectoriel</small>
        </div>
        
        <div class="option-group">
            <label for="qr-error-level">
                <i class="fas fa-shield-alt"></i>
                Correction d'erreur
            </label>
            <select id="qr-error-level" name="qr_error_level" class="form-control">
                <option value="L">Faible (7%) - QR plus dense, moins résistant</option>
                <option value="M" selected>★ Moyenne (15%) - Parfait équilibre</option>
                <option value="Q">Élevée (25%) - Résiste aux rayures/salissures</option>
                <option value="H">Maximum (30%) - Survit même partiellement effacé</option>
            </select>
            <small class="option-help">Plus élevé = plus résistant aux dégâts, mais QR plus gros</small>
        </div>
        
        <div class="option-group">
            <label for="qr-margin">
                <i class="fas fa-border-style"></i>
                Marge
            </label>
            <select id="qr-margin" name="qr_margin" class="form-control">
                <option value="0">Aucune marge - QR collé aux bords</option>
                <option value="1">Petite marge - Bordure fine</option>
                <option value="2" selected>★ Marge normale - Recommandée</option>
                <option value="4">Grande marge - Plus d'espace blanc</option>
            </select>
            <small class="option-help">L'espace blanc aide les scanners à détecter le QR</small>
        </div>
    </div>
    
    <!-- Guide pratique des options -->
    <div class="options-guide">
        <h4><i class="fas fa-lightbulb"></i> Configurations recommandées</h4>
        <div class="guide-grid">
            <div class="guide-item">
                <div class="guide-icon">📱</div>
                <h5>Réseaux sociaux</h5>
                <p><strong>200px</strong> • PNG • Moyenne • Normale</p>
                <small>Instagram, Facebook, Twitter</small>
            </div>
            <div class="guide-item">
                <div class="guide-icon">🖨️</div>
                <h5>Impression papier</h5>
                <p><strong>500px</strong> • PNG • Élevée • Grande</p>
                <small>Flyers, affiches, cartes de visite</small>
            </div>
            <div class="guide-item">
                <div class="guide-icon">📧</div>
                <h5>Email/Site web</h5>
                <p><strong>300px</strong> • JPG • Moyenne • Normale</p>
                <small>Newsletter, site internet</small>
            </div>
            <div class="guide-item">
                <div class="guide-icon">♾️</div>
                <h5>Logo vectoriel</h5>
                <p><strong>400px</strong> • SVG • Élevée • Petite</p>
                <small>Intégration dans designs</small>
            </div>
        </div>
    </div>
    
    <!-- Options avancées pour les utilisateurs premium -->
    <?php if ($user && $user['plan_type'] === 'premium'): ?>
        <div class="advanced-options" style="margin-top: 2rem;">
            <h4><i class="fas fa-star"></i> Options Premium</h4>
            <div class="options-grid">
                <div class="option-group">
                    <label for="qr-color-fg">
                        <i class="fas fa-palette"></i>
                        Couleur du QR Code
                    </label>
                    <input type="color" id="qr-color-fg" name="qr_color_fg" class="form-control" value="#000000">
                </div>
                
                <div class="option-group">
                    <label for="qr-color-bg">
                        <i class="fas fa-fill"></i>
                        Couleur d'arrière-plan
                    </label>
                    <input type="color" id="qr-color-bg" name="qr_color_bg" class="form-control" value="#FFFFFF">
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Champ caché pour stocker le type de QR Code sélectionné -->
<input type="hidden" id="qr-type" name="qr_type" value="url">

<style>
/* Styles spécifiques au formulaire QR Code */
.qr-type-selector {
    margin-bottom: 2rem;
}

.qr-type-selector h3 {
    margin-bottom: 1rem;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.type-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
}

.type-option {
    background: var(--background-white);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 1.5rem 1rem;
    text-align: center;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    font-weight: 500;
}

.type-option:hover {
    border-color: var(--primary-color);
    background: var(--primary-light);
    transform: translateY(-2px);
}

.type-option.active {
    border-color: var(--primary-color);
    background: var(--primary-color);
    color: white;
    box-shadow: var(--shadow-md);
}

.type-option i {
    font-size: 1.75rem;
    margin-bottom: 0.25rem;
}

.type-option span {
    font-size: 0.9rem;
    font-weight: 600;
}

.qr-input-forms {
    margin-bottom: 2rem;
}

.input-form {
    display: none;
    background: var(--background-light);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 2rem;
    animation: fadeIn 0.3s ease;
}

.input-form.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.input-form .form-group {
    margin-bottom: 1.5rem;
}

.input-form .form-group:last-child {
    margin-bottom: 0;
}

.input-form label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--text-dark);
}

.input-form label i {
    color: var(--primary-color);
    width: 16px;
    text-align: center;
}

.form-help {
    margin-top: 0.25rem;
    font-size: 0.85rem;
    color: var(--text-light);
    font-style: italic;
}

.qr-options {
    background: var(--background-light);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 2rem;
    margin-bottom: 2rem;
}

.qr-options h3 {
    margin-bottom: 1.5rem;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.options-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.option-group {
    display: flex;
    flex-direction: column;
}

.option-group label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--text-dark);
    font-size: 0.9rem;
}

.option-group label i {
    color: var(--primary-color);
    width: 16px;
    text-align: center;
}

.option-help {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.8rem;
    color: var(--text-light);
    font-style: italic;
    background: var(--background-light);
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius-sm);
    border-left: 3px solid var(--primary-color);
}

/* Guide pratique */
.options-guide {
    background: linear-gradient(135deg, var(--background-light), var(--primary-light));
    border: 2px solid var(--primary-color);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    margin-top: 2rem;
}

.options-guide h4 {
    margin-bottom: 1rem;
    color: var(--primary-color);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.guide-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.guide-item {
    background: white;
    padding: 1rem;
    border-radius: var(--radius);
    text-align: center;
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
}

.guide-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.guide-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.guide-item h5 {
    margin: 0.5rem 0;
    color: var(--text-dark);
    font-size: 0.9rem;
    font-weight: 600;
}

.guide-item p {
    margin: 0.5rem 0;
    font-size: 0.8rem;
    color: var(--text-medium);
    font-weight: 500;
}

.guide-item small {
    color: var(--text-light);
    font-size: 0.7rem;
    font-style: italic;
}

.advanced-options {
    border-top: 2px solid var(--warning-color);
    padding-top: 2rem;
    background: linear-gradient(135deg, var(--warning-light), transparent);
    border-radius: var(--radius);
    padding: 1.5rem;
}

.advanced-options h4 {
    color: var(--warning-color);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Styles pour les checkboxes */
.input-form label input[type="checkbox"] {
    margin-right: 0.5rem;
    width: auto;
    height: auto;
}

/* Responsive */
@media (max-width: 768px) {
    .type-options {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    }
    
    .type-option {
        padding: 1rem 0.75rem;
    }
    
    .type-option i {
        font-size: 1.5rem;
    }
    
    .options-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .guide-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
    
    .guide-item {
        padding: 0.75rem;
    }
    
    .guide-icon {
        font-size: 1.5rem;
    }
    
    .input-form {
        padding: 1.5rem;
    }
    
    .qr-options {
        padding: 1.5rem;
    }
}

@media (max-width: 480px) {
    .type-options {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .type-option {
        padding: 0.75rem 0.5rem;
    }
    
    .type-option span {
        font-size: 0.8rem;
    }
    
    .guide-grid {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    
    .guide-item {
        padding: 0.5rem;
    }
    
    .options-guide {
        padding: 1rem;
    }
}
</style>

<script>
// JavaScript pour la gestion des types de QR Code
document.addEventListener('DOMContentLoaded', function() {
    const typeOptions = document.querySelectorAll('.type-option');
    const inputForms = document.querySelectorAll('.input-form');
    const qrTypeInput = document.getElementById('qr-type');
    
    let currentType = 'url';

    // Gestion de la sélection du type de QR Code
    typeOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Retirer la sélection précédente
            typeOptions.forEach(opt => opt.classList.remove('active'));
            inputForms.forEach(form => form.classList.remove('active'));
            
            // Ajouter la nouvelle sélection
            this.classList.add('active');
            currentType = this.dataset.type;
            qrTypeInput.value = currentType;
            
            // Afficher le bon formulaire
            const targetForm = document.getElementById(currentType + '-form');
            if (targetForm) {
                targetForm.classList.add('active');
                
                // Focus sur le premier champ requis
                const firstInput = targetForm.querySelector('input[required], textarea[required]');
                if (firstInput) {
                    setTimeout(() => firstInput.focus(), 300);
                }
            }
        });
    });

    // Validation en temps réel
    const inputs = document.querySelectorAll('.input-form input, .input-form textarea, .input-form select');
    inputs.forEach(input => {
        input.addEventListener('input', validateField);
        input.addEventListener('blur', validateField);
    });

    function validateField(e) {
        const field = e.target;
        const group = field.closest('.form-group');
        
        // Retirer les anciennes classes de validation
        group.classList.remove('has-error', 'has-success');
        
        // Validation simple
        if (field.hasAttribute('required') && !field.value.trim()) {
            if (field.closest('.input-form').classList.contains('active')) {
                group.classList.add('has-error');
            }
        } else if (field.value.trim()) {
            group.classList.add('has-success');
        }
    }
});
</script>