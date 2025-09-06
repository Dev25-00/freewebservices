<!-- services/forms/test-form.php -->
<!-- Formulaire de test simple -->

<div class="test-form-section">
    <div class="form-group">
        <label for="test-input">
            <i class="fas fa-flask"></i>
            Test du système
        </label>
        <input type="text" id="test-input" name="test_input" class="form-control" 
               placeholder="Entrez du texte pour tester" required>
        <small class="form-help">Ce champ teste la validation et les styles</small>
    </div>
    
    <div class="form-group">
        <label for="test-select">
            <i class="fas fa-list"></i>
            Sélection de test
        </label>
        <select id="test-select" name="test_select" class="form-control">
            <option value="option1">Option 1</option>
            <option value="option2">Option 2</option>
            <option value="option3">Option 3</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="test-textarea">
            <i class="fas fa-align-left"></i>
            Zone de texte
        </label>
        <textarea id="test-textarea" name="test_textarea" class="form-control" rows="3" 
                  placeholder="Entrez votre message de test..."></textarea>
    </div>
    
    <div class="test-checkboxes">
        <h4>Options de test</h4>
        <label class="checkbox-label">
            <input type="checkbox" name="test_option[]" value="header">
            <span class="checkmark"></span>
            Tester le header
        </label>
        <label class="checkbox-label">
            <input type="checkbox" name="test_option[]" value="footer">
            <span class="checkmark"></span>
            Tester le footer
        </label>
        <label class="checkbox-label">
            <input type="checkbox" name="test_option[]" value="responsive">
            <span class="checkmark"></span>
            Tester le responsive
        </label>
    </div>
</div>

<div class="test-info">
    <div class="info-box">
        <h4><i class="fas fa-info-circle"></i> Informations de test</h4>
        <ul>
            <li><strong>Répertoire actuel :</strong> <?php echo dirname($_SERVER['PHP_SELF']); ?></li>
            <li><strong>Base path :</strong> <?php echo isset($base_path) ? $base_path : 'non défini'; ?></li>
            <li><strong>Page actuelle :</strong> <?php echo basename($_SERVER['PHP_SELF']); ?></li>
            <li><strong>User agent :</strong> Mobile : <?php echo (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false) ? 'Oui' : 'Non'; ?></li>
        </ul>
    </div>
</div>

<style>
.test-form-section {
    background: var(--background-light);
    padding: 2rem;
    border-radius: var(--radius-lg);
    border: 2px solid var(--border-color);
    margin-bottom: 2rem;
}

.test-checkboxes {
    margin-top: 1.5rem;
    padding: 1rem;
    background: var(--background-white);
    border-radius: var(--radius);
    border: 1px solid var(--border-color);
}

.test-checkboxes h4 {
    margin-bottom: 1rem;
    color: var(--text-dark);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    cursor: pointer;
    font-weight: 500;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: var(--primary-color);
}

.test-info {
    background: var(--info-light);
    border: 2px solid var(--info-color);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
}

.info-box h4 {
    color: var(--info-color);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-box ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.info-box li {
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(59, 130, 246, 0.2);
    font-family: monospace;
    font-size: 0.9rem;
}

.info-box li:last-child {
    border-bottom: none;
}

/* Validation states */
.form-group.has-success .form-control {
    border-color: var(--success-color);
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.form-group.has-error .form-control {
    border-color: var(--error-color);
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

/* Responsive */
@media (max-width: 768px) {
    .test-form-section {
        padding: 1.5rem;
    }
    
    .info-box li {
        font-size: 0.8rem;
        word-break: break-all;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const testInput = document.getElementById('test-input');
    const testTextarea = document.getElementById('test-textarea');
    
    // Validation en temps réel
    [testInput, testTextarea].forEach(field => {
        field.addEventListener('input', function() {
            const group = this.closest('.form-group');
            if (this.value.trim().length > 0) {
                group.classList.add('has-success');
                group.classList.remove('has-error');
            } else {
                group.classList.remove('has-success');
                group.classList.remove('has-error');
            }
        });
        
        field.addEventListener('blur', function() {
            const group = this.closest('.form-group');
            if (this.hasAttribute('required') && !this.value.trim()) {
                group.classList.add('has-error');
                group.classList.remove('has-success');
            }
        });
    });
    
    // Affichage d'infos de debug
    console.log('Template de test chargé');
    console.log('Répertoire:', '<?php echo dirname($_SERVER['PHP_SELF']); ?>');
    console.log('Base path:', '<?php echo isset($base_path) ? $base_path : 'non défini'; ?>');
});
</script>