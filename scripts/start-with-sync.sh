#!/bin/bash
# Script de lancement complet avec système de synchronisation
# Ce script démarre le backend Laravel, le frontend Vue.js et le worker de synchronisation

echo -e "\033[32m[START] Démarrage des serveurs avec système de synchronisation...\033[0m"

# Récupérer le répertoire du projet
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

# Vérifier si nous sommes dans le bon répertoire
if [[ ! -f "$PROJECT_ROOT/backend/artisan" ]] || [[ ! -f "$PROJECT_ROOT/frontend/package.json" ]]; then
    echo -e "\033[31m[ERROR] Ce script doit être exécuté depuis la racine du projet\033[0m"
    echo -e "\033[33m[INFO] Conseil: Exécutez d'abord ./scripts/init.sh pour initialiser le projet\033[0m"
    exit 1
fi

echo -e "\033[36m[INFO] Répertoire du projet: $PROJECT_ROOT\033[0m"

# Vérifier si les dépendances sont installées
if [[ ! -d "$PROJECT_ROOT/backend/vendor" ]]; then
    echo -e "\033[31m[ERROR] Les dépendances PHP ne sont pas installées\033[0m"
    echo -e "\033[33m[INFO] Exécutez ./scripts/init.sh pour initialiser le projet\033[0m"
    exit 1
fi

if [[ ! -d "$PROJECT_ROOT/frontend/node_modules" ]]; then
    echo -e "\033[31m[ERROR] Les dépendances Node.js du frontend ne sont pas installées\033[0m"
    echo -e "\033[33m[INFO] Exécutez ./scripts/init.sh pour initialiser le projet\033[0m"
    exit 1
fi

# Créer des fichiers PID pour gérer les processus
PID_DIR="$PROJECT_ROOT/.pids"
mkdir -p "$PID_DIR"

# Fonction de nettoyage
cleanup() {
    echo -e "\n\033[33m[STOP] Arrêt de tous les services...\033[0m"
    
    # Arrêter tous les processus
    if [[ -f "$PID_DIR/laravel.pid" ]]; then
        kill $(cat "$PID_DIR/laravel.pid") 2>/dev/null
        rm -f "$PID_DIR/laravel.pid"
    fi
    
    if [[ -f "$PID_DIR/vue.pid" ]]; then
        kill $(cat "$PID_DIR/vue.pid") 2>/dev/null
        rm -f "$PID_DIR/vue.pid"
    fi
    
    if [[ -f "$PID_DIR/worker.pid" ]]; then
        kill $(cat "$PID_DIR/worker.pid") 2>/dev/null
        rm -f "$PID_DIR/worker.pid"
    fi
    
    if [[ -f "$PID_DIR/scheduler.pid" ]]; then
        kill $(cat "$PID_DIR/scheduler.pid") 2>/dev/null
        rm -f "$PID_DIR/scheduler.pid"
    fi
    
    # Nettoyer le répertoire PID
    rm -rf "$PID_DIR"
    
    echo -e "\033[32m[OK] Tous les services arrêtés\033[0m"
    exit 0
}

# Configurer le signal de nettoyage
trap cleanup SIGINT SIGTERM

echo -e "\033[32m[START] Lancement de tous les services...\033[0m"
echo -e "\033[36m[BACKEND] Laravel: http://localhost:8000\033[0m"
echo -e "\033[36m[FRONTEND] Vue.js: http://localhost:5173\033[0m"
echo -e "\033[36m[SYNC] Worker de synchronisation\033[0m"
echo -e "\033[36m[SCHEDULE] Planificateur de tâches\033[0m"
echo -e "\n\033[33m[INFO] Appuyez sur Ctrl+C pour arrêter tous les services\033[0m\n"

# Démarrer le serveur Laravel
echo -e "\033[33m[LARAVEL] Démarrage du serveur Laravel...\033[0m"
cd "$PROJECT_ROOT/backend"
php artisan serve --host=127.0.0.1 --port=8000 > /tmp/laravel.log 2>&1 &
echo $! > "$PID_DIR/laravel.pid"

# Démarrer le serveur Vue.js
echo -e "\033[33m[VUE] Démarrage du serveur Vue.js...\033[0m"
cd "$PROJECT_ROOT/frontend"
npm run dev > /tmp/vue.log 2>&1 &
echo $! > "$PID_DIR/vue.pid"

# Démarrer le worker de synchronisation
echo -e "\033[33m[WORKER] Démarrage du worker de synchronisation...\033[0m"
cd "$PROJECT_ROOT/backend"
php artisan queue:work --sleep=3 --tries=3 --max-time=3600 --memory=512 > /tmp/worker.log 2>&1 &
echo $! > "$PID_DIR/worker.pid"

# Démarrer le planificateur Laravel
echo -e "\033[33m[SCHEDULER] Démarrage du planificateur Laravel...\033[0m"
cd "$PROJECT_ROOT/backend"
(while true; do php artisan schedule:run; sleep 60; done) > /tmp/scheduler.log 2>&1 &
echo $! > "$PID_DIR/scheduler.pid"

# Attendre un moment pour que les services démarrent
sleep 3

echo -e "\033[32m[OK] Tous les services sont démarrés\033[0m"

# Monitoring des logs en temps réel
echo -e "\033[33m[INFO] Monitoring des services... (Ctrl+C pour arrêter)\033[0m"

# Fonction pour afficher les logs avec couleurs
show_logs() {
    local service=$1
    local logfile=$2
    local color=$3
    
    if [[ -f "$logfile" ]]; then
        tail -n 0 -f "$logfile" 2>/dev/null | while read line; do
            echo -e "\033[${color}m[$service] $line\033[0m"
        done &
    fi
}

# Démarrer l'affichage des logs
show_logs "Laravel" "/tmp/laravel.log" "34"    # Bleu
show_logs "Vue.js" "/tmp/vue.log" "32"         # Vert
show_logs "Worker" "/tmp/worker.log" "35"      # Magenta
show_logs "Scheduler" "/tmp/scheduler.log" "36" # Cyan

# Vérifier périodiquement l'état des services
while true; do
    sleep 5
    
    # Vérifier si tous les processus sont toujours en cours
    if [[ ! -f "$PID_DIR/laravel.pid" ]] || ! kill -0 $(cat "$PID_DIR/laravel.pid" 2>/dev/null) 2>/dev/null; then
        echo -e "\033[31m[ERROR] Le serveur Laravel s'est arrêté\033[0m"
        break
    fi
    
    if [[ ! -f "$PID_DIR/vue.pid" ]] || ! kill -0 $(cat "$PID_DIR/vue.pid" 2>/dev/null) 2>/dev/null; then
        echo -e "\033[31m[ERROR] Le serveur Vue.js s'est arrêté\033[0m"
        break
    fi
    
    if [[ ! -f "$PID_DIR/worker.pid" ]] || ! kill -0 $(cat "$PID_DIR/worker.pid" 2>/dev/null) 2>/dev/null; then
        echo -e "\033[31m[ERROR] Le worker de synchronisation s'est arrêté\033[0m"
        break
    fi
    
    if [[ ! -f "$PID_DIR/scheduler.pid" ]] || ! kill -0 $(cat "$PID_DIR/scheduler.pid" 2>/dev/null) 2>/dev/null; then
        echo -e "\033[31m[ERROR] Le planificateur s'est arrêté\033[0m"
        break
    fi
done

# Si on arrive ici, c'est qu'un service s'est arrêté
cleanup
