// Fix pour les champs required dans le générateur QR
document.addEventListener('DOMContentLoaded', function() {
    const typeOptions = document.querySelectorAll('.type-option');
    const inputForms = document.querySelectorAll('.input-form');
    const qrTypeInput = document.getElementById('qr-type');
    
    let currentType = 'url';

    // Fonction pour gérer les attributs required
    function updateRequiredFields() {
        inputForms.forEach(form => {
            const isActive = form.classList.contains('active');
            const requiredFields = form.querySelectorAll('input[required], textarea[required], select[required]');
            
            requiredFields.forEach(field => {
                if (isActive) {
                    // Remettre required si le formulaire est actif
                    if (field.getAttribute('data-was-required') === 'true') {
                        field.setAttribute('required', 'required');
                    }
                } else {
                    // Sauvegarder l'état required et le retirer si le formulaire est caché
                    if (field.hasAttribute('required')) {
                        field.setAttribute('data-was-required', 'true');
                        field.removeAttribute('required');
                    }
                }
            });
        });
    }

    // Initialiser les champs required au chargement
    updateRequiredFields();

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
                
                // Mettre à jour les champs required après le changement
                setTimeout(() => {
                    updateRequiredFields();
                    
                    // Focus sur le premier champ requis
                    const firstInput = targetForm.querySelector('input[required], textarea[required]');
                    if (firstInput) {
                        firstInput.focus();
                    }
                }, 100);
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
        
        if (!group) return;
        
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

    // Gestion de la soumission du formulaire
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // S'assurer que seuls les champs actifs sont validés
            updateRequiredFields();
            
            // Validation finale
            const activeForm = document.querySelector('.input-form.active');
            if (activeForm) {
                const requiredFields = activeForm.querySelectorAll('input[required], textarea[required], select[required]');
                let hasErrors = false;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.closest('.form-group').classList.add('has-error');
                        hasErrors = true;
                    }
                });
                
                if (hasErrors) {
                    e.preventDefault();
                    // Scroll vers la première erreur
                    const firstError = activeForm.querySelector('.has-error input, .has-error textarea, .has-error select');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                }
            }
        });
    }
});
