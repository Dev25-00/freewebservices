// assets/js/services/hashtag-generator-script.js
// JavaScript spécifique pour le générateur de hashtags avec formatage multi-plateforme

document.addEventListener('DOMContentLoaded', function() {
    const serviceForm = document.getElementById('serviceForm');
    const submitBtn = document.getElementById('submitBtn');
    const resultSection = document.getElementById('resultSection');
    const resultContent = document.getElementById('resultContent');
    
    // Surcharger la fonction de traitement du service générique
    if (serviceForm) {
        serviceForm.removeEventListener('submit', handleServiceSubmit);
        serviceForm.addEventListener('submit', handleHashtagSubmit);
    }
    
    function handleHashtagSubmit(e) {
        e.preventDefault();
        
        // Récupérer les données du formulaire
        const formData = new FormData(e.target);
        const keywords = formData.get('keywords');
        
        if (!keywords || keywords.trim() === '') {
            showNotification('Veuillez entrer au moins un mot-clé', 'error');
            return;
        }
        
        // Afficher l'état de chargement
        showButtonLoading(submitBtn);
        
        // Préparer les données pour l'API
        const requestData = {
            keywords: keywords,
            category: formData.get('category') || '',
            quantity: parseInt(formData.get('quantity')) || 30,
            popularity: formData.get('popularity') || 'mixed',
            platform: formData.get('platform') || 'all'
        };
        
        // Stocker la plateforme sélectionnée pour le formatage
        window.selectedPlatform = requestData.platform;
        
        // Appel à l'API
        fetch('../api/generate-hashtags.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            hideButtonLoading(submitBtn);
            
            if (data.success) {
                displayHashtags(data.hashtags, data.count, requestData.platform);
                showNotification(`${data.count} hashtags générés avec succès!`, 'success');
            } else {
                showNotification(data.message || 'Erreur lors de la génération', 'error');
            }
        })
        .catch(error => {
            hideButtonLoading(submitBtn);
            console.error('Erreur:', error);
            showNotification('Erreur de connexion', 'error');
        });
    }
    
    function displayHashtags(hashtags, count, platform) {
        if (!resultSection || !resultContent) return;
        
        // Déterminer le format par défaut selon la plateforme
        let defaultFormat = 'space';
        switch(platform) {
            case 'youtube':
                defaultFormat = 'youtube';
                break;
            case 'tiktok':
                defaultFormat = 'tiktok';
                break;
            case 'instagram':
                defaultFormat = 'instagram';
                break;
            case 'linkedin':
                defaultFormat = 'newline';
                break;
            case 'facebook':
                defaultFormat = 'facebook';
                break;
            case 'pinterest':
                defaultFormat = 'pinterest';
                break;
            default:
                defaultFormat = 'space';
        }
        
        // Créer l'interface d'affichage des hashtags
        const hashtagsHTML = `
            <div class="hashtags-result">
                <div class="hashtags-header">
                    <div class="hashtags-title">
                        <h4><i class="fas fa-hashtag"></i> ${count} hashtags générés</h4>
                    </div>
                    <div class="hashtags-controls">
                        <div class="format-selector">
                            <label for="copyFormat" class="format-label">
                                <i class="fas fa-cog"></i> Format de copie :
                            </label>
                            <select id="copyFormat" class="form-control form-control-sm">
                                <option value="space" ${defaultFormat === 'space' ? 'selected' : ''}>Séparés par espaces</option>
                                <option value="newline" ${defaultFormat === 'newline' ? 'selected' : ''}>Un par ligne</option>
                                <option value="comma" ${defaultFormat === 'comma' ? 'selected' : ''}>Séparés par virgules</option>
                                <option value="semicolon" ${defaultFormat === 'semicolon' ? 'selected' : ''}>Séparés par point-virgules</option>
                                <option value="youtube" ${defaultFormat === 'youtube' ? 'selected' : ''}>📺 YouTube (optimisé)</option>
                                <option value="tiktok" ${defaultFormat === 'tiktok' ? 'selected' : ''}>🎵 TikTok (optimisé)</option>
                                <option value="instagram" ${defaultFormat === 'instagram' ? 'selected' : ''}>📸 Instagram (optimisé)</option>
                                <option value="linkedin" ${defaultFormat === 'linkedin' ? 'selected' : ''}>💼 LinkedIn (optimisé)</option>
                                <option value="facebook" ${defaultFormat === 'facebook' ? 'selected' : ''}>🔵 Facebook (optimisé)</option>
                                <option value="pinterest" ${defaultFormat === 'pinterest' ? 'selected' : ''}>📌 Pinterest (optimisé)</option>
                            </select>
                        </div>
                        <div class="hashtags-actions">
                            <button class="btn btn-primary btn-small" id="copyAllBtn">
                                <i class="fas fa-copy"></i> Copier tout
                            </button>
                            <button class="btn btn-outline btn-small" id="copySelectedBtn" disabled>
                                <i class="fas fa-check-square"></i> Copier sélectionnés (<span id="selectedCount">0</span>)
                            </button>
                            <button class="btn btn-outline btn-small" id="clearSelectionBtn">
                                <i class="fas fa-times"></i> Tout désélectionner
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="format-preview" id="formatPreview">
                    <div class="preview-header">
                        <i class="fas fa-eye"></i> Aperçu du format sélectionné :
                    </div>
                    <div class="preview-content" id="previewContent">
                        ${formatHashtags(hashtags.slice(0, 3), defaultFormat)}
                    </div>
                </div>
                
                <div class="hashtags-grid" id="hashtagsGrid">
                    ${hashtags.map((hashtag, index) => `
                        <div class="hashtag-item" data-hashtag="${hashtag}" data-index="${index}">
                            <span class="hashtag-text">${hashtag}</span>
                            <div class="hashtag-controls">
                                <button class="hashtag-select-btn" title="Sélectionner/Désélectionner">
                                    <i class="far fa-square"></i>
                                </button>
                                <button class="hashtag-copy-btn" title="Copier ce hashtag">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    `).join('')}
                </div>
                
                <div class="hashtags-footer">
                    <div class="hashtags-stats">
                        <span><strong>Total:</strong> ${count} hashtags</span>
                        <span><strong>Sélectionnés:</strong> <span id="selectedTotal">0</span></span>
                    </div>
                    <div class="hashtags-tips">
                        <p><i class="fas fa-info-circle"></i> <strong>Astuce:</strong> Sélectionnez le format selon votre plateforme. YouTube préfère les hashtags avec des sauts de ligne ou point-virgules.</p>
                    </div>
                </div>
            </div>
        `;
        
        resultContent.innerHTML = hashtagsHTML;
        resultSection.style.display = 'block';
        resultSection.scrollIntoView({ behavior: 'smooth' });
        
        // Ajouter les événements pour l'interaction avec les hashtags
        setupHashtagInteractions(hashtags);
    }
    
    function formatHashtags(hashtags, format) {
        switch(format) {
            case 'space':
                return hashtags.join(' ');
            case 'newline':
                return hashtags.join('\n');
            case 'comma':
                return hashtags.join(', ');
            case 'semicolon':
                return hashtags.join('; ');
            case 'youtube':
                // Format optimisé pour YouTube: hashtags séparés par des sauts de ligne avec espacement
                return hashtags.join('\n\n');
            case 'tiktok':
                // Format optimisé pour TikTok: hashtags séparés par espaces
                return hashtags.join(' ');
            case 'instagram':
                // Format optimisé pour Instagram: groupe de 5 hashtags par ligne
                const instagramGroups = [];
                for (let i = 0; i < hashtags.length; i += 5) {
                    instagramGroups.push(hashtags.slice(i, i + 5).join(' '));
                }
                return instagramGroups.join('\n\n');
            case 'linkedin':
                // Format optimisé pour LinkedIn: hashtags séparés par des sauts de ligne
                return hashtags.join('\n');
            case 'facebook':
                // Format optimisé pour Facebook: hashtags séparés par espaces avec quelques sauts de ligne
                const facebookGroups = [];
                for (let i = 0; i < hashtags.length; i += 3) {
                    facebookGroups.push(hashtags.slice(i, i + 3).join(' '));
                }
                return facebookGroups.join('\n\n');
            case 'pinterest':
                // Format optimisé pour Pinterest: groupe de 3-4 hashtags par ligne
                const pinterestGroups = [];
                for (let i = 0; i < hashtags.length; i += 4) {
                    pinterestGroups.push(hashtags.slice(i, i + 4).join(' '));
                }
                return pinterestGroups.join('\n');
            default:
                return hashtags.join(' ');
        }
    }
    
    function setupHashtagInteractions(allHashtags) {
        const hashtagItems = document.querySelectorAll('.hashtag-item');
        const copyAllBtn = document.getElementById('copyAllBtn');
        const copySelectedBtn = document.getElementById('copySelectedBtn');
        const clearSelectionBtn = document.getElementById('clearSelectionBtn');
        const selectedCountSpan = document.getElementById('selectedCount');
        const selectedTotalSpan = document.getElementById('selectedTotal');
        const copyFormatSelect = document.getElementById('copyFormat');
        const previewContent = document.getElementById('previewContent');
        
        let selectedHashtags = new Set();
        
        // Mise à jour de l'aperçu selon le format sélectionné
        copyFormatSelect.addEventListener('change', function() {
            const format = this.value;
            const previewHashtags = allHashtags.slice(0, 3);
            previewContent.textContent = formatHashtags(previewHashtags, format);
        });
        
        // Gestion de la sélection des hashtags
        hashtagItems.forEach(item => {
            const selectBtn = item.querySelector('.hashtag-select-btn');
            const copyBtn = item.querySelector('.hashtag-copy-btn');
            const hashtag = item.dataset.hashtag;
            
            // Sélection par clic sur l'item ou le bouton
            [item, selectBtn].forEach(element => {
                element.addEventListener('click', (e) => {
                    if (e.target.closest('.hashtag-copy-btn')) return;
                    
                    e.preventDefault();
                    toggleHashtagSelection(item, hashtag, selectedHashtags, selectedCountSpan, selectedTotalSpan, copySelectedBtn);
                });
            });
            
            // Copie individuelle
            copyBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                copyToClipboard(hashtag);
                showNotification(`${hashtag} copié!`, 'success');
                
                // Animation de succès
                copyBtn.classList.add('copied');
                setTimeout(() => copyBtn.classList.remove('copied'), 400);
            });
        });
        
        // Copier tous les hashtags avec le format sélectionné
        copyAllBtn.addEventListener('click', () => {
            const format = copyFormatSelect.value;
            const formattedText = formatHashtags(allHashtags, format);
            copyToClipboard(formattedText);
            
            const formatNames = {
                'space': 'avec espaces',
                'newline': 'ligne par ligne',
                'comma': 'avec virgules',
                'semicolon': 'avec point-virgules',
                'youtube': 'format YouTube',
                'tiktok': 'format TikTok',
                'instagram': 'format Instagram',
                'linkedin': 'format LinkedIn',
                'facebook': 'format Facebook',
                'pinterest': 'format Pinterest'
            };
            
            showNotification(`${allHashtags.length} hashtags copiés ${formatNames[format]}!`, 'success');
        });
        
        // Copier les hashtags sélectionnés avec le format sélectionné
        copySelectedBtn.addEventListener('click', () => {
            if (selectedHashtags.size > 0) {
                const format = copyFormatSelect.value;
                const selectedArray = Array.from(selectedHashtags);
                const formattedText = formatHashtags(selectedArray, format);
                copyToClipboard(formattedText);
                
                const formatNames = {
                    'space': 'avec espaces',
                    'newline': 'ligne par ligne', 
                    'comma': 'avec virgules',
                    'semicolon': 'avec point-virgules',
                    'youtube': 'format YouTube',
                    'tiktok': 'format TikTok',
                    'instagram': 'format Instagram',
                    'linkedin': 'format LinkedIn',
                    'facebook': 'format Facebook',
                    'pinterest': 'format Pinterest'
                };
                
                showNotification(`${selectedHashtags.size} hashtags sélectionnés copiés ${formatNames[format]}!`, 'success');
            }
        });
        
        // Tout désélectionner
        clearSelectionBtn.addEventListener('click', () => {
            selectedHashtags.clear();
            hashtagItems.forEach(item => {
                item.classList.remove('selected');
                const icon = item.querySelector('.hashtag-select-btn i');
                icon.className = 'far fa-square';
            });
            updateSelectionUI(selectedHashtags, selectedCountSpan, selectedTotalSpan, copySelectedBtn);
        });
    }
    
    function toggleHashtagSelection(item, hashtag, selectedHashtags, selectedCountSpan, selectedTotalSpan, copySelectedBtn) {
        const icon = item.querySelector('.hashtag-select-btn i');
        
        if (selectedHashtags.has(hashtag)) {
            selectedHashtags.delete(hashtag);
            item.classList.remove('selected');
            icon.className = 'far fa-square';
        } else {
            selectedHashtags.add(hashtag);
            item.classList.add('selected');
            icon.className = 'fas fa-check-square';
        }
        
        updateSelectionUI(selectedHashtags, selectedCountSpan, selectedTotalSpan, copySelectedBtn);
    }
    
    function updateSelectionUI(selectedHashtags, selectedCountSpan, selectedTotalSpan, copySelectedBtn) {
        const count = selectedHashtags.size;
        selectedCountSpan.textContent = count;
        selectedTotalSpan.textContent = count;
        copySelectedBtn.disabled = count === 0;
    }
    
    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text);
        } else {
            // Fallback pour les navigateurs plus anciens
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
            } catch (err) {
                console.error('Erreur lors de la copie:', err);
            }
            document.body.removeChild(textArea);
        }
    }
    
    function showButtonLoading(button) {
        if (!button) return;
        
        button.disabled = true;
        const originalHTML = button.innerHTML;
        button.dataset.originalHtml = originalHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération...';
    }
    
    function hideButtonLoading(button) {
        if (!button) return;
        
        button.disabled = false;
        if (button.dataset.originalHtml) {
            button.innerHTML = button.dataset.originalHtml;
        }
    }
    
    function showNotification(message, type = 'info') {
        // Utiliser le système de notifications s'il existe
        if (window.miniServices && window.miniServices.showNotification) {
            window.miniServices.showNotification(message, type);
        } else {
            // Fallback simple
            alert(message);
        }
    }
});
