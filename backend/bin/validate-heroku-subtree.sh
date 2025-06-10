#!/bin/bash

# Script de validation pré-déploiement Heroku avec Git Subtree
# Vérifie que tout est prêt pour le déploiement

echo "🔍 Validation pré-déploiement Heroku avec Git Subtree..."

ERRORS=0
WARNINGS=0

# Fonction pour afficher les erreurs
error() {
    echo "❌ $1" 
    ERRORS=$((ERRORS + 1))
}

# Fonction pour afficher les avertissements
warning() {
    echo "⚠️  $1"
    WARNINGS=$((WARNINGS + 1))
}

# Fonction pour afficher les succès
success() {
    echo "✅ $1"
}

echo ""
echo "📋 Vérifications système..."

# 1. Vérifier Heroku CLI
if command -v heroku &> /dev/null; then
    HEROKU_VERSION=$(heroku --version)
    success "Heroku CLI installé: $HEROKU_VERSION"
else
    error "Heroku CLI non installé"
fi

# 2. Vérifier Git
if command -v git &> /dev/null; then
    GIT_VERSION=$(git --version)
    success "Git installé: $GIT_VERSION"
else
    error "Git non installé"
fi

# 3. Vérifier que nous sommes dans la racine du projet
if [[ $(basename "$PWD") == "backend" ]]; then
    error "Vous êtes dans le dossier backend. Remontez à la racine du projet."
elif [[ ! -d "backend" ]]; then
    error "Dossier backend non trouvé. Assurez-vous d'être à la racine du projet."
else
    success "Position correcte à la racine du projet"
fi

echo ""
echo "📁 Vérifications structure du projet..."

# 4. Vérifier les fichiers backend essentiels
if [[ -f "backend/composer.json" ]]; then
    success "composer.json trouvé dans backend/"
else
    error "composer.json manquant dans backend/"
fi

if [[ -f "backend/Procfile" ]]; then
    success "Procfile trouvé dans backend/"
else
    error "Procfile manquant dans backend/"
fi

if [[ -f "backend/artisan" ]]; then
    success "Laravel artisan trouvé dans backend/"
else
    error "Fichier artisan Laravel manquant dans backend/"
fi

# 5. Vérifier la configuration PostgreSQL
if [[ -f "backend/config/database.php" ]]; then
    if grep -q "pgsql" "backend/config/database.php"; then
        success "Configuration PostgreSQL détectée"
    else
        warning "Configuration PostgreSQL non détectée dans database.php"
    fi
else
    error "Fichier database.php manquant"
fi

# 6. Vérifier le HerokuProductionSeeder
if [[ -f "backend/database/seeders/HerokuProductionSeeder.php" ]]; then
    success "HerokuProductionSeeder trouvé"
else
    warning "HerokuProductionSeeder manquant"
fi

echo ""
echo "🔗 Vérifications Git..."

# 7. Vérifier que c'est un repo Git
if git status &> /dev/null; then
    success "Repository Git valide"
    
    # 8. Vérifier les changements non commités
    if git diff --quiet && git diff --cached --quiet; then
        success "Aucun changement non commité"
    else
        warning "Il y a des changements non commités. Les scripts peuvent les commiter automatiquement."
    fi
    
    # 9. Vérifier la branche actuelle
    CURRENT_BRANCH=$(git branch --show-current)
    if [[ "$CURRENT_BRANCH" == "main" ]] || [[ "$CURRENT_BRANCH" == "master" ]]; then
        success "Branche principale active: $CURRENT_BRANCH"
    else
        warning "Branche actuelle: $CURRENT_BRANCH (recommandé: main/master)"
    fi
    
else
    error "Pas un repository Git valide"
fi

echo ""
echo "🐘 Vérifications Heroku (optionnelles)..."

# 10. Vérifier la connexion Heroku
if command -v heroku &> /dev/null; then
    if heroku auth:whoami &> /dev/null; then
        HEROKU_USER=$(heroku auth:whoami)
        success "Connecté à Heroku: $HEROKU_USER"
    else
        warning "Non connecté à Heroku (utilisez: heroku login)"
    fi
fi

echo ""
echo "📜 Vérifications scripts de déploiement..."

# 11. Vérifier les scripts
if [[ -f "backend/bin/deploy-heroku-subtree.sh" ]]; then
    if [[ -x "backend/bin/deploy-heroku-subtree.sh" ]]; then
        success "Script Bash prêt et exécutable"
    else
        warning "Script Bash non exécutable (chmod +x backend/bin/deploy-heroku-subtree.sh)"
    fi
else
    error "Script deploy-heroku-subtree.sh manquant"
fi

if [[ -f "backend/bin/deploy-heroku-subtree.ps1" ]]; then
    success "Script PowerShell disponible"
else
    error "Script deploy-heroku-subtree.ps1 manquant"
fi

echo ""
echo "📊 Résumé de la validation..."

if [[ $ERRORS -eq 0 ]] && [[ $WARNINGS -eq 0 ]]; then
    echo "🎉 EXCELLENT! Tout est prêt pour le déploiement Heroku."
    echo ""
    echo "💡 Commandes de déploiement:"
    echo "   ./backend/bin/deploy-heroku-subtree.sh votre-app-name"
    echo "   .\backend\bin\deploy-heroku-subtree.ps1 votre-app-name"
elif [[ $ERRORS -eq 0 ]]; then
    echo "✅ PRÊT avec avertissements ($WARNINGS warnings)"
    echo ""
    echo "💡 Vous pouvez déployer, mais vérifiez les avertissements ci-dessus."
    echo "   ./backend/bin/deploy-heroku-subtree.sh votre-app-name"
else
    echo "❌ NON PRÊT - $ERRORS erreurs, $WARNINGS avertissements"
    echo ""
    echo "🔧 Corrigez les erreurs ci-dessus avant de déployer."
fi

echo ""
echo "📚 Documentation:"
echo "   docs/DEPLOIEMENT_HEROKU_GIT_SUBTREE.md"
echo "   docs/MIGRATION_SQLITE_POSTGRESQL_GUIDE.md"

exit $ERRORS
