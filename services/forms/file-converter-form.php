<!-- Formulaire de base - L'interface avancée sera créée par JavaScript -->
<div class="form-group">
    <label for="outputFormat">Format de sortie</label>
    <select name="outputFormat" id="outputFormat" class="form-control" required>
        <option value="">Choisir un format</option>
        <optgroup label="Images" data-group="image">
            <option value="jpg">JPG</option>
            <option value="png">PNG</option>
            <option value="gif">GIF</option>
            <option value="webp">WebP</option>
            <option value="bmp">BMP</option>
        </optgroup>
        <optgroup label="Documents" data-group="document">
            <option value="pdf">PDF</option>
            <option value="txt">TXT</option>
            <option value="rtf">RTF</option>
        </optgroup>
        <optgroup label="Audio" data-group="audio">
            <option value="mp3">MP3</option>
            <option value="wav">WAV</option>
            <option value="ogg">OGG</option>
            <option value="flac">FLAC</option>
        </optgroup>
        <optgroup label="Vidéo" data-group="video">
            <option value="mp4">MP4</option>
            <option value="avi">AVI</option>
            <option value="webm">WebM</option>
            <option value="mov">MOV</option>
        </optgroup>
    </select>
</div>

<div class="form-group">
    <label for="quality">Qualité</label>
    <select name="quality" id="quality" class="form-control">
        <option value="high">Haute qualité</option>
        <option value="medium" selected>Qualité moyenne (recommandée)</option>
        <option value="low">Qualité basse (fichier plus petit)</option>
    </select>
</div>

<div class="form-group">
    <p class="help-text">
        <i class="fas fa-info-circle"></i>
        Sélectionnez d'abord un fichier ci-dessus, puis choisissez le format de conversion souhaité.
    </p>
</div>

<!-- Preview area (added) -->
<div id="filePreviewArea" class="mb-3" style="display:none;">
    <h5>Aperçu</h5>
    <div id="filePreview" style="min-height:120px;"></div>
    <div class="mt-2">
        <button id="downloadConvertedBtn" type="button" class="btn btn-success" style="display:none;">
            <i class="fas fa-download"></i> Télécharger la conversion
        </button>
        <span id="conversionNotice" class="ms-2 text-muted" style="display:none;"></span>
    </div>
</div>
