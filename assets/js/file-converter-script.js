// File converter client script - final
document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('fileInput');
    const uploadZone = document.getElementById('uploadZone');
    const serviceForm = document.getElementById('serviceForm');
    const outputFormatSelect = document.getElementById('outputFormat');
    const qualitySelect = document.getElementById('quality');
    const previewArea = document.getElementById('filePreviewArea');
    const previewContainer = document.getElementById('filePreview');
    const downloadBtn = document.getElementById('downloadConvertedBtn');
    const conversionNotice = document.getElementById('conversionNotice');
    const detectedTypeEl = document.getElementById('detectedType');

    const groups = {
        image: ['jpg','jpeg','png','gif','webp','bmp','tiff','svg'],
        document: ['pdf','doc','docx','txt','rtf','odt'],
        audio: ['mp3','wav','ogg','flac','aac','m4a'],
        video: ['mp4','avi','mkv','webm','mov','wmv','flv']
    };

    const getExt = name => (name || '').split('.').pop().toLowerCase();
    const detectGroup = ext => { for (const k of Object.keys(groups)) if (groups[k].includes(ext)) return k; return null; };

    function setDetectedType(text) { if (!detectedTypeEl) return; detectedTypeEl.textContent = text; detectedTypeEl.style.display = text ? '' : 'none'; }

    function showPreview(file) {
        if (!previewContainer) return; previewContainer.innerHTML = '';
        if (previewArea) previewArea.style.display = '';
        const ext = getExt(file.name);
        if (groups.image.includes(ext)) {
            const img = document.createElement('img'); img.src = URL.createObjectURL(file); img.style.maxWidth = '100%'; img.style.maxHeight = '360px'; previewContainer.appendChild(img);
        } else {
            const p = document.createElement('p'); p.textContent = file.name + ' (' + Math.round(file.size/1024) + ' KB)'; previewContainer.appendChild(p);
        }
    }

    function filterOutputOptions(group) {
        if (!outputFormatSelect) return;
        Array.from(outputFormatSelect.querySelectorAll('optgroup')).forEach(og => {
            const g = og.getAttribute('data-group');
            og.style.display = (g === group) ? '' : 'none';
        });
        outputFormatSelect.value = '';
    }

    function notify(msg, isError = false) {
        if (!conversionNotice) return; conversionNotice.style.display = ''; conversionNotice.textContent = msg; conversionNotice.classList.toggle('text-danger', !!isError);
    }

    async function uploadAndConvert(file, targetFormat) {
        const fd = new FormData(); fd.append('file', file); fd.append('target_format', targetFormat);
        notify('Envoi du fichier...');
        try {
            const resp = await fetch('/miniservices/api/convert-file.php', { method: 'POST', body: fd });
            const json = await resp.json();
            if (json.success) {
                const basename = json.download_name;
                if (downloadBtn) { downloadBtn.style.display = ''; downloadBtn.dataset.file = basename; }
                notify('Fichier prêt — cliquez sur Télécharger.');
            } else {
                notify('Erreur: ' + (json.message || 'Conversion échouée'), true);
            }
        } catch (e) {
            notify('Erreur réseau: envoi échoué', true);
        }
    }

    function selectFile(file) {
        showPreview(file);
        const ext = getExt(file.name);
        const group = detectGroup(ext);
        setDetectedType(group ? group.toUpperCase() : 'INCONNU');
        if (group) filterOutputOptions(group);
        if (downloadBtn) downloadBtn.style.display = 'none';
        if (serviceForm) serviceForm._selectedFile = file;
    }

    if (uploadZone) {
        uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('dragover'); });
        uploadZone.addEventListener('dragleave', e => { e.preventDefault(); uploadZone.classList.remove('dragover'); });
        uploadZone.addEventListener('drop', e => { e.preventDefault(); uploadZone.classList.remove('dragover'); const f = e.dataTransfer.files && e.dataTransfer.files[0]; if (f) selectFile(f); });
    }
    if (fileInput) fileInput.addEventListener('change', e => { const f = e.target.files && e.target.files[0]; if (f) selectFile(f); });

    if (serviceForm) serviceForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const file = serviceForm._selectedFile || (fileInput && fileInput.files && fileInput.files[0]);
        if (!file) return notify('Veuillez sélectionner un fichier.', true);
        const target = outputFormatSelect ? outputFormatSelect.value : '';
        if (!target) return notify('Sélectionnez un format de sortie.', true);

        const ext = getExt(file.name);
        const group = detectGroup(ext);
        if (group === 'image') {
            try {
                notify('Conversion client en cours...');
                const converted = await convertImageClientSide(file, target, qualitySelect ? qualitySelect.value : 'medium');
                const blobUrl = URL.createObjectURL(converted.blob);
                const a = document.createElement('a'); a.href = blobUrl; a.download = converted.convertedName; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(blobUrl);
                notify('Fichier converti localement et téléchargé.');
                return;
            } catch (err) {
                console.warn('Client conversion failed, fallback to server:', err);
            }
        }

        await uploadAndConvert(file, target);
    });

    if (downloadBtn) downloadBtn.addEventListener('click', () => {
        const file = downloadBtn.dataset.file;
        if (!file) return notify('Aucun fichier à télécharger', true);
        const url = '/miniservices/api/download.php?file=' + encodeURIComponent(file);
        window.open(url, '_blank');
        fetch('/miniservices/api/mark-download.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ file }) }).catch(() => {});
        notify('Téléchargement lancé. Le fichier sera supprimé automatiquement après quelques minutes.');
    });

    function convertImageClientSide(file, targetFormat, quality) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = function () {
                const canvas = document.createElement('canvas');
                canvas.width = img.naturalWidth; canvas.height = img.naturalHeight;
                const ctx = canvas.getContext('2d'); ctx.drawImage(img, 0, 0);
                let mime = 'image/png';
                if (targetFormat === 'jpg' || targetFormat === 'jpeg') mime = 'image/jpeg';
                if (targetFormat === 'webp') mime = 'image/webp';
                const q = quality === 'high' ? 0.95 : (quality === 'low' ? 0.6 : 0.8);
                canvas.toBlob((blob) => {
                    if (!blob) return reject(new Error('Échec de la conversion'));
                    const convertedName = file.name.replace(/\.[^/.]+$/, '') + '.' + (targetFormat === 'jpeg' ? 'jpg' : targetFormat);
                    resolve({ blob, convertedName, mime });
                }, mime, q);
            };
            img.onerror = () => reject(new Error('Impossible de charger l\'image pour conversion'));
            img.src = URL.createObjectURL(file);
        });
    }
});
            return false;
        }

        const extension = file.name.split('.').pop().toLowerCase();
        const allowedExtensions = Object.values(this.formatCategories)
            .flatMap(cat => cat.from);

        if (!allowedExtensions.includes(extension)) {
            this.showError('Format de fichier non supporté.');
            return false;
        }

        return true;
    }

    showFileInfo(file) {
        // Créer l'info du fichier si elle n'existe pas
        let fileInfoEl = document.querySelector('.uploaded-file-info');
        if (!fileInfoEl) {
            const uploadSection = document.querySelector('.upload-section');
            const fileInfoHTML = `
                <div class="uploaded-file-info">
                    <div class="file-details">
                        <div class="file-icon" id="fileIcon">
                            <i class="fas fa-file"></i>
                        </div>
                        <div class="file-meta">
                            <div class="file-name" id="fileName"></div>
                            <div class="file-size" id="fileSize"></div>
                            <div class="file-type" id="fileType"></div>
                        </div>
                        <button type="button" class="remove-file" id="removeFile">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            uploadSection.insertAdjacentHTML('beforeend', fileInfoHTML);
            
            // Event listener pour supprimer le fichier
            document.getElementById('removeFile').addEventListener('click', () => this.removeFile());
            fileInfoEl = document.querySelector('.uploaded-file-info');
        }

        // Mettre à jour les informations
        const extension = file.name.split('.').pop().toLowerCase();
        const iconClass = this.getFileIcon(extension);
        
        document.getElementById('fileIcon').innerHTML = `<i class="${iconClass}"></i>`;
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = this.formatFileSize(file.size);
        document.getElementById('fileType').textContent = extension.toUpperCase();
        
        fileInfoEl.classList.add('show');
        this.uploadZone.style.display = 'none';
    }

    updateFormatOptions(file) {
        const extension = file.name.split('.').pop().toLowerCase();
        
        // Désactiver tous les formats d'abord
        document.querySelectorAll('.format-option').forEach(option => {
            option.classList.add('disabled');
        });

        // Activer seulement les formats de conversion possibles
        Object.values(this.formatCategories).forEach(category => {
            if (category.from.includes(extension)) {
                category.formats.forEach(format => {
                    if (format !== extension) { // Ne pas permettre la conversion vers le même format
                        const optionEl = document.querySelector(`[data-format="${format}"]`);
                        if (optionEl) {
                            optionEl.classList.remove('disabled');
                        }
                    }
                });
            }
        });

        // Activer le sélecteur de format
        const formatSelector = document.getElementById('formatSelector');
        if (formatSelector) {
            formatSelector.classList.add('active');
        }
    }

    removeFile() {
        this.uploadedFile = null;
        this.selectedFormat = null;
        
        // Masquer les infos du fichier
        const fileInfoEl = document.querySelector('.uploaded-file-info');
        if (fileInfoEl) {
            fileInfoEl.classList.remove('show');
        }
        
        // Réafficher la zone d'upload
        this.uploadZone.style.display = 'block';
        
        // Réinitialiser les sélections
        document.querySelectorAll('.format-option.selected').forEach(opt => opt.classList.remove('selected'));
        document.querySelectorAll('.format-option').forEach(opt => opt.classList.add('disabled'));
        
        const formatSelector = document.getElementById('formatSelector');
        if (formatSelector) {
            formatSelector.classList.remove('active');
        }
        
        this.updateUI();
    }

    handleFormSubmit(e) {
        e.preventDefault();
        
        if (!this.uploadedFile || !this.selectedFormat) {
            this.showError('Veuillez sélectionner un fichier et un format de sortie.');
            return;
        }

        if (this.conversionInProgress) {
            return;
        }

        this.startConversion();
    }

    handleFormatChange(e) {
        this.selectedFormat = e.target.value;
        this.updateUI();
    }

    handleQualityChange(e) {
        this.selectedQuality = e.target.value;
    }

    startConversion() {
        this.conversionInProgress = true;
        this.hideError();
        this.showProgress();
        
        const formData = new FormData();
        formData.append('file', this.uploadedFile);
        formData.append('target_format', this.selectedFormat);
        formData.append('quality', this.selectedQuality);

        fetch('/miniservices/api/convert-file.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            this.conversionInProgress = false;
            this.hideProgress();
            
            if (data.success) {
                this.showResult(data);
            } else {
                this.showError(data.message || 'Erreur lors de la conversion.');
            }
        })
        .catch(error => {
            this.conversionInProgress = false;
            this.hideProgress();
            this.showError('Erreur de connexion. Veuillez réessayer.');
            console.error('Erreur:', error);
        });
    }

    showProgress() {
        const progressEl = document.getElementById('conversionProgress');
        if (progressEl) {
            progressEl.classList.add('show');
        }

        // Simulation du progrès
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 20;
            if (progress > 90) progress = 90;
            
            this.updateProgress(progress, 'Conversion en cours...', 'Traitement du fichier');
            
            if (!this.conversionInProgress || progress >= 90) {
                clearInterval(interval);
                if (this.conversionInProgress) {
                    this.updateProgress(100, 'Finalisation...', 'Préparation du téléchargement');
                }
            }
        }, 300);
    }

    updateProgress(percent, status, detail) {
        const progressText = document.getElementById('progressText');
        const progressRing = document.getElementById('progressRingProgress');
        const statusEl = document.getElementById('conversionStatus');
        const detailEl = document.getElementById('conversionDetail');
        
        if (progressText) progressText.textContent = Math.round(percent) + '%';
        if (progressRing) {
            const circumference = 2 * Math.PI * 35;
            const offset = circumference - (percent / 100) * circumference;
            progressRing.style.strokeDashoffset = offset;
        }
        if (statusEl) statusEl.textContent = status;
        if (detailEl) detailEl.textContent = detail;
    }

    hideProgress() {
        const progressEl = document.getElementById('conversionProgress');
        if (progressEl) {
            progressEl.classList.remove('show');
        }
    }

    showResult(data) {
        const resultEl = document.getElementById('conversionResult');
        const resultDetails = document.getElementById('resultDetails');
        const downloadBtn = document.getElementById('downloadBtn');
        
        if (resultDetails) {
            resultDetails.innerHTML = `
                Fichier converti de <strong>${data.original_format?.toUpperCase()}</strong> 
                vers <strong>${data.target_format?.toUpperCase()}</strong>
                <br><small>Nom du fichier : ${data.filename}</small>
            `;
        }
        
        if (downloadBtn) {
            downloadBtn.href = `/miniservices/api/download.php?file=${encodeURIComponent(data.converted_file_path)}`;
            downloadBtn.download = data.filename;
        }
        
        if (resultEl) {
            resultEl.classList.add('show');
            resultEl.scrollIntoView({ behavior: 'smooth' });
        }
    }

    showError(message) {
        const errorEl = document.getElementById('conversionError');
        const errorMessage = document.getElementById('errorMessage');
        
        if (errorMessage) errorMessage.textContent = message;
        if (errorEl) errorEl.classList.add('show');
    }

    hideError() {
        const errorEl = document.getElementById('conversionError');
        if (errorEl) errorEl.classList.remove('show');
    }

    resetConverter() {
        this.removeFile();
        this.hideError();
        this.hideProgress();
        
        const resultEl = document.getElementById('conversionResult');
        if (resultEl) resultEl.classList.remove('show');
        
        // Reset form
        if (this.fileInput) this.fileInput.value = '';
        if (this.outputFormatSelect) this.outputFormatSelect.value = '';
        if (this.qualitySelect) this.qualitySelect.value = 'medium';
        
        // Reset quality selection
        document.querySelectorAll('.quality-option').forEach(opt => opt.classList.remove('selected'));
        document.querySelector('[data-quality="medium"]')?.classList.add('selected');
        this.selectedQuality = 'medium';
    }

    updateUI() {
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            if (this.uploadedFile && this.selectedFormat && !this.conversionInProgress) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('disabled');
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.add('disabled');
            }
        }
    }

    // Utilitaires
    getFileIcon(extension) {
        const iconMap = {
            // Images
            'jpg': 'fas fa-image', 'jpeg': 'fas fa-image', 'png': 'fas fa-image', 
            'gif': 'fas fa-image', 'webp': 'fas fa-image', 'bmp': 'fas fa-image',
            // Documents
            'pdf': 'fas fa-file-pdf', 'doc': 'fas fa-file-word', 'docx': 'fas fa-file-word',
            'txt': 'fas fa-file-alt', 'rtf': 'fas fa-file-alt',
            // Audio
            'mp3': 'fas fa-music', 'wav': 'fas fa-music', 'ogg': 'fas fa-music', 'flac': 'fas fa-music',
            // Vidéo
            'mp4': 'fas fa-video', 'avi': 'fas fa-video', 'mkv': 'fas fa-video', 'webm': 'fas fa-video'
        };
        
        return iconMap[extension] || 'fas fa-file';
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
}

// Utilitaires globaux pour le convertisseur
window.FileConverter = FileConverter;

document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('fileInput');
    const outputFormatSelect = document.getElementById('outputFormat');
    const previewArea = document.getElementById('filePreviewArea');
    const previewContainer = document.getElementById('filePreview');
    const downloadBtn = document.getElementById('downloadConvertedBtn');
    const conversionNotice = document.getElementById('conversionNotice');
    const serviceForm = document.getElementById('serviceForm');
    const qualitySelect = document.getElementById('quality');

    if (!fileInput || !outputFormatSelect) return;

    // Mapping mime -> groups
    const mimeToGroup = (mime) => {
        if (!mime) return 'unknown';
        if (mime.startsWith('image/')) return 'image';
        if (mime.startsWith('video/')) return 'video';
        if (mime.startsWith('audio/')) return 'audio';
        if (mime === 'application/pdf' || mime === 'application/x-pdf') return 'document';
        if (mime.startsWith('text/')) return 'document';
        return 'other';
    };

    // Show preview for various types
    function showPreview(file) {
        previewContainer.innerHTML = '';
        previewArea.style.display = '';
        downloadBtn.style.display = 'none';
        conversionNotice.style.display = 'none';

        const mime = file.type || '';
        const group = mimeToGroup(mime);
        const url = URL.createObjectURL(file);

        if (group === 'image') {
            const img = document.createElement('img');
            img.src = url;
            img.style.maxWidth = '100%';
            img.style.maxHeight = '360px';
            img.alt = file.name;
            previewContainer.appendChild(img);
        } else if (group === 'video') {
            const video = document.createElement('video');
            video.controls = true;
            video.src = url;
            video.style.maxWidth = '100%';
            video.style.maxHeight = '360px';
            previewContainer.appendChild(video);
        } else if (group === 'audio') {
            const audio = document.createElement('audio');
            audio.controls = true;
            audio.src = url;
            previewContainer.appendChild(audio);
        } else if (mime === 'application/pdf') {
            const embed = document.createElement('embed');
            embed.src = url;
            embed.type = 'application/pdf';
            embed.style.width = '100%';
            embed.style.height = '480px';
            previewContainer.appendChild(embed);
        } else {
            // generic info
            const info = document.createElement('div');
            info.innerHTML = `<strong>${file.name}</strong> <small class="text-muted">(${Math.round(file.size/1024)} KB, ${file.type || 'inconnu'})</small>`;
            previewContainer.appendChild(info);
        }
    }

    // Filter options in outputFormatSelect based on group
    function filterOutputOptions(group) {
        // Show only optgroups matching data-group attribute and their options
        const optgroups = Array.from(outputFormatSelect.querySelectorAll('optgroup'));
        // Build list of allowed values
        optgroups.forEach(og => {
            const ogGroup = og.getAttribute('data-group');
            Array.from(og.children).forEach(opt => {
                opt.hidden = (ogGroup !== group);
            });
        });

        // If nothing visible, enable all and show notice
        const anyVisible = Array.from(outputFormatSelect.options).some(o => !o.hidden && o.value);
        if (!anyVisible) {
            // fallback: show all
            Array.from(outputFormatSelect.options).forEach(o => o.hidden = false);
            conversionNotice.textContent = 'Aucune conversion automatique supportée pour ce type ; la conversion nécessitera un traitement serveur.';
            conversionNotice.style.display = '';
        } else {
            conversionNotice.style.display = 'none';
        }

        // Reset selection
        outputFormatSelect.value = '';
    }

    // Convert image client-side and provide download link
    function convertImageClientSide(file, targetFormat, quality) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = function () {
                const canvas = document.createElement('canvas');
                canvas.width = img.naturalWidth;
                canvas.height = img.naturalHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0);
                let mimeType = 'image/png';
                if (targetFormat === 'jpg' || targetFormat === 'jpeg') mimeType = 'image/jpeg';
                else if (targetFormat === 'webp') mimeType = 'image/webp';
                else if (targetFormat === 'gif') mimeType = 'image/gif';
                else if (targetFormat === 'bmp') mimeType = 'image/bmp';

                const q = (quality === 'low') ? 0.6 : (quality === 'high' ? 0.95 : 0.8);

                canvas.toBlob(function (blob) {
                    if (!blob) return reject(new Error('Échec de la conversion'));
                    const convertedName = file.name.replace(/\.[^/.]+$/, '') + '.' + (targetFormat === 'jpeg' ? 'jpg' : targetFormat);
                    resolve({ blob, convertedName, mimeType });
                }, mimeType, q);
            };
            img.onerror = function () {
                reject(new Error('Impossible de charger l\'image pour conversion'));
            };
            img.src = URL.createObjectURL(file);
        });
    }

    // Handle file selection
    fileInput.addEventListener('change', function (e) {
        const file = (e.target.files && e.target.files[0]) ? e.target.files[0] : null;
        if (!file) {
            previewArea.style.display = 'none';
            return;
        }
        showPreview(file);
        const group = mimeToGroup(file.type);
        filterOutputOptions(group);

        // If image and a default suggestion, select a recommended format
        if (group === 'image') {
            // prefer png if original is not png
            const ext = file.name.split('.').pop().toLowerCase();
            const prefer = ext === 'png' ? 'png' : 'webp';
            if (Array.from(outputFormatSelect.options).some(o => !o.hidden && o.value === prefer)) {
                outputFormatSelect.value = prefer;
            }
        }
    });

    // When user clicks downloadConvertedBtn, trigger conversion of currently selected file
    downloadBtn.addEventListener('click', async function () {
        const file = fileInput.files[0];
        const target = outputFormatSelect.value;
        const quality = qualitySelect ? qualitySelect.value : 'medium';
        if (!file || !target) return alert('Sélectionnez un fichier et un format de sortie.');

        const group = mimeToGroup(file.type);
        if (group === 'image') {
            // client-side conversion
            downloadBtn.disabled = true;
            downloadBtn.textContent = 'Conversion...';
            try {
                const res = await convertImageClientSide(file, target, quality);
                const url = URL.createObjectURL(res.blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = res.convertedName;
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(url);
            } catch (err) {
                alert('Erreur lors de la conversion : ' + err.message);
            } finally {
                downloadBtn.disabled = false;
                downloadBtn.innerHTML = '<i class="fas fa-download"></i> Télécharger la conversion';
            }
        } else {
            // For other types, we don't implement client-side conversion here
            alert('La conversion de ce type est pour l\'instant traitée côté serveur. Le bouton soumettra le fichier pour traitement serveur si implémenté.');
            // If you later implement server endpoint, submit form via fetch with FormData including desired outputFormat
        }
    });

    // Intercept form submission to either perform client-side conversion or send to server
    serviceForm && serviceForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const file = fileInput.files[0];
        const target = outputFormatSelect.value;
        if (!file) return alert('Aucun fichier sélectionné.');
        if (!target) return alert('Sélectionnez un format de sortie.');

        const group = mimeToGroup(file.type);

        if (group === 'image') {
            // perform conversion and trigger download (or also send to server)
            try {
                const res = await convertImageClientSide(file, target, qualitySelect.value);
                const url = URL.createObjectURL(res.blob);
                // show converted preview & enable download button
                previewContainer.innerHTML = '';
                const img = document.createElement('img');
                img.src = url;
                img.style.maxWidth = '100%';
                previewContainer.appendChild(img);

                downloadBtn.style.display = '';
                downloadBtn.onclick = function () {
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = res.convertedName;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                };

                conversionNotice.textContent = 'Conversion effectuée localement. Cliquez sur "Télécharger la conversion".';
                conversionNotice.style.display = '';
            } catch (err) {
                alert('Erreur conversion : ' + err.message);
            }
        } else {
            // fallback: submit to server endpoint if exists
            // Build FormData and POST to server endpoint (not yet implemented)
            alert('Conversion côté serveur non configurée. Vous pouvez implémenter /api/convert.php pour traiter ce fichier.');
        }
    });

    // Enable download button when user selects output format (for converted preview)
    outputFormatSelect.addEventListener('change', function () {
        const file = fileInput.files[0];
        if (!file) return;
        const group = mimeToGroup(file.type);
        if (group === 'image') {
            downloadBtn.style.display = '';
            downloadBtn.textContent = '<i class=\"fas fa-download\"></i> Télécharger la conversion';
            // keep actual action tied to conversion on submit or click
        } else {
            downloadBtn.style.display = 'none';
        }
    });
});
