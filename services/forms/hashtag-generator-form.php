<!-- services/forms/hashtag-generator-form.php -->
<!-- Formulaire spécifique pour le générateur de hashtags -->

<div class="hashtag-input-section">
    <div class="form-group">
        <label for="keywords">
            <i class="fas fa-hashtag"></i>
            Entrez vos mots-clés
        </label>
        <div class="hashtag-input-wrapper">
            <input type="text" id="keywords" name="keywords" class="form-control" 
                   placeholder="Exemple: cuisine, recette, délicieux, maison"
                   autocomplete="off" required>
            <div class="hashtag-suggestions" id="suggestions" style="display: none;">
                <!-- Suggestions dynamiques -->
            </div>
        </div>
        <small class="form-help">Séparez vos mots-clés par des virgules</small>
    </div>
</div>

<div class="hashtag-options">
    <h3><i class="fas fa-cog"></i> Options de génération</h3>
    <div class="options-grid">
        <div class="option-group">
            <label for="category">
                <i class="fas fa-folder"></i>
                Catégorie
            </label>
            <select id="category" name="category" class="form-control">
                <option value="">Toutes catégories</option>
                <option value="food">🍽️ Cuisine & Food</option>
                <option value="travel">✈️ Voyage & Nature</option>
                <option value="fitness">💪 Sport & Fitness</option>
                <option value="business">💼 Business & Entrepreneur</option>
                <option value="technology">💻 Technologie & Innovation</option>
                <option value="lifestyle">❤️ Mode de vie</option>
                <option value="art">🎨 Art & Créativité</option>
                <option value="fashion">👗 Mode & Beauté</option>
            </select>
        </div>

        <div class="option-group">
            <label for="quantity">
                <i class="fas fa-list-ol"></i>
                Nombre de hashtags
            </label>
            <select id="quantity" name="quantity" class="form-control">
                <option value="10">10 hashtags</option>
                <option value="20">20 hashtags</option>
                <option value="30" selected>30 hashtags</option>
                <option value="50">50 hashtags</option>
            </select>
        </div>

        <div class="option-group">
            <label for="popularity">
                <i class="fas fa-chart-line"></i>
                Type de popularité
            </label>
            <select id="popularity" name="popularity" class="form-control">
                <option value="mixed" selected>🔄 Mélangé (Recommandé)</option>
                <option value="popular">🔥 Populaires (> 1M posts)</option>
                <option value="moderate">⚖️ Modérés (100K - 1M posts)</option>
                <option value="niche">🎯 Niches (< 100K posts)</option>
            </select>
        </div>
        
        <div class="option-group">
            <label for="platform">
                <i class="fas fa-share-alt"></i>
                Plateforme cible
            </label>
            <select id="platform" name="platform" class="form-control">
                <option value="all">Toutes plateformes</option>
                <option value="youtube">📺 YouTube</option>
                <option value="instagram">📸 Instagram</option>
                <option value="twitter">🐦 Twitter</option>
                <option value="tiktok">🎵 TikTok</option>
                <option value="linkedin">💼 LinkedIn</option>
                <option value="facebook">🔵 Facebook</option>
                <option value="pinterest">📌 Pinterest</option>
            </select>
        </div>
    </div>
</div>

<!-- Catégories populaires -->
<div class="popular-categories">
    <h3><i class="fas fa-fire"></i> Catégories populaires</h3>
    <div class="category-buttons">
        <button type="button" class="category-btn" data-category="food" data-keywords="food, foodie, delicious, yummy, cooking, recipe">
            <i class="fas fa-utensils"></i>
            <span>Cuisine</span>
        </button>
        <button type="button" class="category-btn" data-category="travel" data-keywords="travel, wanderlust, adventure, nature, explore, vacation">
            <i class="fas fa-plane"></i>
            <span>Voyage</span>
        </button>
        <button type="button" class="category-btn" data-category="fitness" data-keywords="fitness, workout, gym, health, motivation, sport">
            <i class="fas fa-dumbbell"></i>
            <span>Fitness</span>
        </button>
        <button type="button" class="category-btn" data-category="business" data-keywords="business, entrepreneur, success, marketing, startup, motivation">
            <i class="fas fa-briefcase"></i>
            <span>Business</span>
        </button>
        <button type="button" class="category-btn" data-category="technology" data-keywords="tech, innovation, coding, digital, AI, technology">
            <i class="fas fa-laptop-code"></i>
            <span>Tech</span>
        </button>
        <button type="button" class="category-btn" data-category="lifestyle" data-keywords="lifestyle, inspiration, daily, life, motivation, happiness">
            <i class="fas fa-heart"></i>
            <span>Lifestyle</span>
        </button>
    </div>
</div>

<style>
/* Styles spécifiques au formulaire hashtag */
.hashtag-input-section {
    margin-bottom: 2rem;
}

.hashtag-input-wrapper {
    position: relative;
}

.hashtag-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--background-white);
    border: 2px solid var(--border-color);
    border-top: none;
    border-radius: 0 0 var(--radius) var(--radius);
    max-height: 200px;
    overflow-y: auto;
    z-index: 100;
    box-shadow: var(--shadow-md);
}

.hashtag-suggestion {
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: var(--transition);
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.hashtag-suggestion:hover {
    background: var(--primary-light);
    color: var(--primary-color);
}

.hashtag-suggestion:last-child {
    border-bottom: none;
}

.hashtag-options {
    background: var(--background-light);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 2rem;
    margin-bottom: 2rem;
}

.hashtag-options h3 {
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

.popular-categories {
    background: var(--background-white);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 2rem;
    margin-bottom: 2rem;
}

.popular-categories h3 {
    margin-bottom: 1.5rem;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.category-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
}

.category-btn {
    background: var(--background-light);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 1rem;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}

.category-btn:hover {
    border-color: var(--primary-color);
    background: var(--primary-light);
    transform: translateY(-2px);
}

.category-btn.active {
    border-color: var(--primary-color);
    background: var(--primary-color);
    color: white;
}

.category-btn i {
    font-size: 1.5rem;
    margin-bottom: 0.25rem;
}

.category-btn span {
    font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 768px) {
    .options-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .category-buttons {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    }
    
    .hashtag-options {
        padding: 1.5rem;
    }
    
    .popular-categories {
        padding: 1.5rem;
    }
}

@media (max-width: 480px) {
    .category-buttons {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .category-btn {
        padding: 0.75rem;
    }
    
    .category-btn i {
        font-size: 1.25rem;
    }
}
</style>

<script>
// JavaScript pour le formulaire hashtag
document.addEventListener('DOMContentLoaded', function() {
    const keywordsInput = document.getElementById('keywords');
    const categorySelect = document.getElementById('category');
    const suggestionsDiv = document.getElementById('suggestions');
    const categoryButtons = document.querySelectorAll('.category-btn');
    
    // Suggestions de mots-clés
    const suggestions = {
        general: ['trending', 'viral', 'popular', 'amazing', 'awesome', 'cool', 'fun', 'love', 'life', 'style'],
        food: ['food', 'foodie', 'delicious', 'yummy', 'cooking', 'recipe', 'chef', 'kitchen', 'meal', 'tasty'],
        travel: ['travel', 'wanderlust', 'adventure', 'explore', 'vacation', 'journey', 'destination', 'nature', 'landscape', 'trip'],
        fitness: ['fitness', 'workout', 'gym', 'health', 'motivation', 'sport', 'training', 'healthy', 'strong', 'fit'],
        business: ['business', 'entrepreneur', 'success', 'marketing', 'startup', 'growth', 'leadership', 'innovation', 'professional', 'career'],
        technology: ['tech', 'innovation', 'coding', 'digital', 'AI', 'technology', 'software', 'programming', 'development', 'future'],
        lifestyle: ['lifestyle', 'inspiration', 'daily', 'motivation', 'happiness', 'mindfulness', 'wellness', 'self-care', 'positive', 'growth'],
        art: ['art', 'creative', 'design', 'artist', 'creativity', 'drawing', 'painting', 'illustration', 'artistic', 'inspiration'],
        fashion: ['fashion', 'style', 'outfit', 'trendy', 'beauty', 'makeup', 'look', 'fashionable', 'chic', 'elegant']
    };

    // Gestion des suggestions en temps réel
    keywordsInput.addEventListener('input', function() {
        const value = this.value.toLowerCase();
        const words = value.split(',');
        const lastWord = words[words.length - 1].trim();
        
        if (lastWord.length > 1) {
            showSuggestions(lastWord);
        } else {
            hideSuggestions();
        }
    });

    function showSuggestions(partial) {
        const category = categorySelect.value || 'general';
        const categoryKeywords = suggestions[category] || suggestions.general;
        const allKeywords = [...suggestions.general, ...categoryKeywords];
        
        const matches = allKeywords
            .filter(keyword => keyword.includes(partial) && !keywordsInput.value.includes(keyword))
            .slice(0, 5);
        
        if (matches.length > 0) {
            suggestionsDiv.innerHTML = '';
            matches.forEach(match => {
                const suggestion = document.createElement('div');
                suggestion.className = 'hashtag-suggestion';
                suggestion.innerHTML = `<i class="fas fa-hashtag"></i>${match}`;
                suggestion.addEventListener('click', () => {
                    addKeyword(match);
                    hideSuggestions();
                });
                suggestionsDiv.appendChild(suggestion);
            });
            suggestionsDiv.style.display = 'block';
        } else {
            hideSuggestions();
        }
    }

    function hideSuggestions() {
        suggestionsDiv.style.display = 'none';
    }

    function addKeyword(keyword) {
        const currentKeywords = keywordsInput.value.split(',').map(k => k.trim()).filter(k => k);
        const lastKeyword = currentKeywords[currentKeywords.length - 1];
        
        if (lastKeyword && !lastKeyword.includes(keyword)) {
            currentKeywords[currentKeywords.length - 1] = keyword;
        } else {
            currentKeywords.push(keyword);
        }
        
        keywordsInput.value = currentKeywords.join(', ') + ', ';
        keywordsInput.focus();
    }

    // Gestion des boutons de catégories
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            const category = this.dataset.category;
            const keywords = this.dataset.keywords;
            
            // Mettre à jour les champs
            keywordsInput.value = keywords;
            categorySelect.value = category;
            
            // Effet visuel
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Focus sur l'input pour continuer à taper
            keywordsInput.focus();
        });
    });

    // Cacher les suggestions en cliquant ailleurs
    document.addEventListener('click', function(e) {
        if (!keywordsInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
            hideSuggestions();
        }
    });

    // Mise à jour des suggestions selon la catégorie
    categorySelect.addEventListener('change', function() {
        // Réinitialiser les boutons de catégorie
        categoryButtons.forEach(btn => btn.classList.remove('active'));
        
        // Activer le bouton correspondant
        const selectedButton = document.querySelector(`[data-category="${this.value}"]`);
        if (selectedButton) {
            selectedButton.classList.add('active');
        }
    });

    // Validation en temps réel
    keywordsInput.addEventListener('blur', function() {
        if (this.value.trim()) {
            this.closest('.form-group').classList.add('has-success');
            this.closest('.form-group').classList.remove('has-error');
        } else {
            this.closest('.form-group').classList.add('has-error');
            this.closest('.form-group').classList.remove('has-success');
        }
    });
});
</script>