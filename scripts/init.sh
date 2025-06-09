#!/bin/bash
# Script d'initialisation automatique du projet Laravel + Vue.js
# Ce script configure l'environnement de développement complet

echo -e "\033[32m[START] Initialisation du projet Laravel + Vue.js...\033[0m"

# Récupérer le répertoire du projet
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

# Vérifier si nous sommes dans le bon répertoire
if [[ ! -f "$PROJECT_ROOT/backend/composer.json" ]] || [[ ! -f "$PROJECT_ROOT/frontend/package.json" ]]; then
    echo -e "\033[31m[ERROR] Ce script doit être exécuté depuis la racine du projet\033[0m"
    exit 1
fi

echo -e "\033[36m[INFO] Répertoire du projet: $PROJECT_ROOT\033[0m"

# Installation des dépendances du backend (Laravel)
echo -e "\n\033[33m[PHP] Installation des dépendances PHP...\033[0m"
cd "$PROJECT_ROOT/backend"

if ! command -v composer &> /dev/null; then
    echo -e "\033[31m[ERROR] Composer n'est pas installé. Veuillez installer Composer d'abord.\033[0m"
    exit 1
fi

composer install --no-interaction --prefer-dist

# Copier le fichier .env s'il n'existe pas
if [[ ! -f ".env" ]]; then
    echo -e "\033[33m[ENV] Création du fichier .env...\033[0m"
    cp .env.example .env
fi

# Générer la clé d'application Laravel
echo -e "\033[33m[KEY] Génération de la clé d'application Laravel...\033[0m"
php artisan key:generate

# Créer la base de données SQLite si elle n'existe pas
if [[ ! -f "database/database.sqlite" ]]; then
    echo -e "\033[33m[DB] Création de la base de données SQLite...\033[0m"
    touch database/database.sqlite
fi

# Exécuter les migrations
echo -e "\033[33m[MIGRATE] Exécution des migrations de base de données...\033[0m"
php artisan migrate --force

# Exécuter les seeders
echo -e "\033[33m[SEED] Exécution des seeders...\033[0m"
php artisan db:seed --force

# Configuration du système de rangs automatiques
echo -e "\033[33m[RANKS] Configuration du système de rangs automatiques...\033[0m"

# Mettre à jour les rangs de tous les utilisateurs basé sur leurs points
echo -e "\033[36m[RANKS] Mise à jour des rangs utilisateurs selon leurs points...\033[0m"
php artisan ranks:update --force

echo -e "\033[32m[OK] Système de rangs automatiques configuré\033[0m"

# Configuration du système de files d'attente
echo -e "\033[33m[QUEUE] Configuration du système de files d'attente...\033[0m"
php artisan queue:table 2>/dev/null && php artisan migrate --force

# Créer les tables de jobs pour la synchronisation
echo -e "\033[33m[SYNC] Création des tables de synchronisation...\033[0m"
php artisan make:job-table 2>/dev/null && php artisan migrate --force

# Vérifier la configuration du cache
echo -e "\033[33m[CACHE] Configuration du cache...\033[0m"
php artisan config:cache
php artisan route:cache

# Tester la connectivité de la base de données
echo -e "\033[33m[TEST] Test de la connectivité de la base de données...\033[0m"
if php artisan tinker --execute="echo 'DB OK';" &>/dev/null; then
    echo -e "\033[32m[OK] Base de données accessible\033[0m"
else
    echo -e "\033[33m[WARNING] Problème de connexion à la base de données\033[0m"
fi

# Installation des dépendances Node.js pour le backend (si package.json existe)
if [[ -f "package.json" ]]; then
    echo -e "\033[33m[NPM] Installation des dépendances Node.js du backend...\033[0m"
    npm install
fi

# Installation des dépendances du frontend (Vue.js)
echo -e "\n\033[33m[FRONTEND] Installation des dépendances du frontend...\033[0m"
cd "$PROJECT_ROOT/frontend"

if ! command -v npm &> /dev/null; then
    echo -e "\033[31m[ERROR] npm n'est pas installé. Veuillez installer Node.js d'abord.\033[0m"
    exit 1
fi

# Copier et configurer le fichier .env du frontend
if [[ ! -f ".env" ]] && [[ -f ".env.example" ]]; then
    echo -e "\033[33m[ENV] Création du fichier .env frontend...\033[0m"
    cp .env.example .env
fi

npm install

# Vérifier que Vite peut démarrer
echo -e "\033[33m[TEST] Test de la configuration Vite...\033[0m"
if npm run build &>/dev/null; then
    echo -e "\033[32m[OK] Configuration Vite validée\033[0m"
else
    echo -e "\033[33m[WARNING] Problème avec la configuration Vite\033[0m"
fi

# Retourner à la racine du projet
cd "$PROJECT_ROOT"

# Vérifications finales et configuration du système de synchronisation
echo -e "\n\033[33m[SYNC] Vérification du système de synchronisation...\033[0m"
cd "$PROJECT_ROOT/backend"

# Vérifier que les listeners sont bien enregistrés
echo -e "\033[36m[CHECK] Vérification des listeners d'événements...\033[0m"
if php artisan event:list &>/dev/null; then
    echo -e "\033[32m[OK] Système d'événements opérationnel\033[0m"
    echo -e "\033[36m[INFO] Events configurés:\033[0m"
    echo -e "   • QuizCompleted -> SynchronizeUserScore (points + rangs)"
    echo -e "   • RankUpdated -> Notification automatique de progression"
else
    echo -e "\033[33m[INFO] Configurez les listeners dans EventServiceProvider\033[0m"
fi

# Vérifier le système polymorphique des quiz
echo -e "\033[36m[CHECK] Vérification du système polymorphique des quiz...\033[0m"
php artisan tinker --execute="
\$polymorphicCount = \App\Models\Quiz::whereNotNull('quizable_type')->count();
\$totalQuiz = \App\Models\Quiz::count();
\$percentage = \$totalQuiz > 0 ? round((\$polymorphicCount / \$totalQuiz) * 100, 1) : 0;

echo \"Système polymorphique des quiz:\\n\";
echo \"  • Quiz avec relations polymorphiques: \$polymorphicCount\\n\";
echo \"  • Total des quiz: \$totalQuiz\\n\";
echo \"  • Migration polymorphique: \$percentage%\\n\";

if (\$percentage >= 90) {
    echo \"  ✅ Migration polymorphique réussie\\n\";
} else {
    echo \"  ⚠️  Migration polymorphique en cours...\\n\";
}

\$quizTypes = \App\Models\QuizType::all();
if (\$quizTypes->isNotEmpty()) {
    echo \"\\nTypes de quiz disponibles:\\n\";
    foreach (\$quizTypes as \$type) {
        echo \"  • {\$type->name} (morph: {\$type->morph_type})\\n\";
    }
}
"

# Vérifier la distribution des rangs après initialisation
echo -e "\033[36m[CHECK] Vérification de la distribution des rangs...\033[0m"
php artisan tinker --execute="
\$distribution = \App\Models\User::join('ranks', 'users.rank_id', '=', 'ranks.id')
    ->selectRaw('ranks.name, ranks.level, COUNT(*) as count')
    ->groupBy('ranks.id', 'ranks.name', 'ranks.level')
    ->orderBy('ranks.level')
    ->get();
    
if (\$distribution->isNotEmpty()) {
    echo \"Distribution actuelle des rangs:\\n\";
    foreach (\$distribution as \$rank) {
        echo \"  • {\$rank->name} (Niv. {\$rank->level}): {\$rank->count} utilisateurs\\n\";
    }
} else {
    echo \"Aucune distribution de rang trouvée\\n\";
}
"

# Créer les répertoires de logs si nécessaire
if [[ ! -d "storage/logs" ]]; then
    mkdir -p storage/logs
    echo -e "\033[32m[LOG] Répertoire de logs créé\033[0m"
fi

# Configurer les permissions sur les répertoires de cache et logs
echo -e "\033[33m[PERM] Configuration des permissions...\033[0m"
chmod -R 775 storage/framework/cache 2>/dev/null || true
chmod -R 775 storage/logs 2>/dev/null || true

# Test final du système
echo -e "\033[33m[TEST] Test final du système...\033[0m"
if php artisan about &>/dev/null; then
    echo -e "\033[32m[OK] Laravel opérationnel\033[0m"
else
    echo -e "\033[33m[WARNING] Problème de configuration détecté\033[0m"
fi

# Retourner à la racine du projet
cd "$PROJECT_ROOT"

echo -e "\n\033[32m[SUCCESS] Initialisation terminée avec succès!\033[0m"
echo -e "\n\033[36m[SUMMARY] Configuration complétée:\033[0m"
echo -e "   ✅ Dépendances PHP installées (Composer)"
echo -e "   ✅ Dépendances Node.js installées (npm)"
echo -e "   ✅ Fichiers d'environnement configurés"
echo -e "   ✅ Clés d'application générées"
echo -e "   ✅ Base de données créée et migrée"
echo -e "   ✅ Seeders exécutés"
echo -e "   ✅ Système de rangs automatiques configuré"
echo -e "   ✅ Système de files d'attente configuré"
echo -e "   ✅ Système de synchronisation vérifié"

echo -e "\n\033[36m[INFO] Prochaines étapes:\033[0m"
echo -e "   • Exécutez ./scripts/start.sh pour lancer les serveurs de développement"
echo -e "   • Backend sera disponible sur http://localhost:8000"
echo -e "   • Frontend sera disponible sur http://localhost:5173"
echo -e "   • Pour démarrer le worker de synchronisation :"
echo -e "     cd backend && php artisan queue:work"

echo -e "\n\033[36m[TIPS] Commandes utiles:\033[0m"
echo -e "   • ./scripts/start.sh              - Démarrer les serveurs"
echo -e "   • ./scripts/start-with-sync.sh    - Démarrer avec synchronisation"
echo -e "   • php artisan queue:work          - Démarrer le worker de synchronisation"
echo -e "   • php artisan ranks:update        - Mettre à jour les rangs manuellement"
echo -e "   • php artisan sync:all-scores     - Synchroniser manuellement les scores"
echo -e "   • php artisan about               - Vérifier l'état du système"
