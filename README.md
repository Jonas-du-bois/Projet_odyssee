# Breitling League

Une application web moderne de gestion de compétitions et tournois, développée avec Laravel et Vue.js pour offrir une expérience utilisateur fluide et performante.

## Description du projet

Breitling League est une plateforme complète dédiée à l'organisation et au suivi de compétitions sportives ou e-sportives. L'application permet de gérer les équipes, les matchs, les classements et les statistiques en temps réel. Construite avec Laravel 10 pour un backend robuste et Vue.js pour une interface utilisateur moderne et réactive.

---

## Table des matières
- [Description du projet](#description-du-projet)
- [Architecture du projet](#architecture-du-projet)
- [Prérequis](#prérequis)
- [Installation](#installation)
  - [Backend](#backend)
  - [Frontend](#frontend)
- [Utilisation](#utilisation)
- [Fonctionnalités clés](#fonctionnalités-clés)
- [Perspectives d'évolution](#perspectives-dévolution)
- [Contribution](#contribution)
- [Licence](#licence)

---

## Architecture du projet

Cette application suit une architecture séparée avec un backend API et un frontend SPA :

- **Backend** : Laravel 10 + PHP 8.4
- **Frontend** : Vue.js avec Node.js 22 et Vite

### Dossiers principaux
- `backend/` : Code source Laravel, migrations, routes API, modèles, contrôleurs
- `frontend/` : Code source Vue.js, composants, routes frontend, configuration npm/vite

### Structure détaillée

**Backend (Laravel):**
- `app/` : Modèles, contrôleurs, middleware, services
- `database/` : Migrations, seeders, factories
- `routes/` : Définition des routes API
- `config/` : Configuration de l'application

**Frontend (Vue.js):**
- `src/components/` : Composants Vue réutilisables
- `src/views/` : Pages de l'application
- `src/router/` : Configuration du routage
- `src/store/` : Gestion d'état (Pinia/Vuex)

---

## Prérequis

Avant de commencer, assurez-vous d'avoir installé :

- **PHP 8.4** ou supérieur
- **Composer** (gestionnaire de dépendances PHP)
- **Node.js 22** (npm inclus)
- **Laravel 10**
- **Base de données** compatible (SQLite)
- **Git** pour le contrôle de version

---

## Installation

### Backend

1. Se positionner dans le dossier backend :
   ```bash
   cd backend
   ```

2. Installer les dépendances PHP avec Composer :
   ```bash
   composer install
   ```

3. Copier le fichier d'environnement et le configurer :
   ```bash
   cp .env.example .env
   ```
   Éditer le fichier `.env` pour configurer :
   - Base de données (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
   - URL de l'application
   - Clés d'API si nécessaire

4. Générer la clé d'application Laravel :
   ```bash
   php artisan key:generate
   ```

5. Exécuter les migrations pour créer la base de données :
   ```bash
   php artisan migrate
   ```

6. (Optionnel) Peupler la base avec des données de test :
   ```bash
   php artisan db:seed
   ```

7. Démarrer le serveur Laravel :
   ```bash
   php artisan serve
   ```
   Le backend sera accessible sur `http://localhost:8000`

### Frontend

1. Se positionner dans le dossier frontend :
   ```bash
   cd frontend
   ```

2. Installer les dépendances Node.js :
   ```bash
   npm install
   ```

3. Copier et configurer le fichier d'environnement :
   ```bash
   cp .env.example .env
   ```
   Configurer l'URL du backend dans le fichier `.env`

4. Démarrer le serveur de développement :
   ```bash
   npm run dev
   ```
   Le frontend sera accessible sur `http://localhost:5173`

---

## Utilisation

1. **Accès à l'application** : Ouvrir `http://localhost:5173` dans votre navigateur
2. **API Backend** : Accessible sur `http://localhost:8000/api`
3. **Documentation API** : Disponible sur `http://localhost:8000/docs`

---

## Contribution

Les contributions sont les bienvenues ! Pour contribuer :

1. **Fork** le projet
2. Créer une branche pour votre fonctionnalité :
   ```bash
   git checkout -b feature/nouvelle-fonctionnalite
   ```
3. **Commiter** vos changements :
   ```bash
   git commit -m "Ajout d'une nouvelle fonctionnalité"
   ```
4. **Push** vers la branche :
   ```bash
   git push origin feature/nouvelle-fonctionnalite
   ```
5. Ouvrir une **Pull Request**

### Standards de code
- Suivre les conventions PSR-12 pour PHP
- Utiliser ESLint/Prettier pour JavaScript
- Écrire des tests pour les nouvelles fonctionnalités
- Documenter les changements importants

---

## Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

## Support

Pour toute question ou problème :
- 📧 Email : support@breitling-league.com
- 🐛 Issues : [GitHub Issues](https://github.com/Jonas-du-bois/Projet_odyssee.git/issues)
- 📖 Documentation : [Wiki du projet](https://github.com/Jonas-du-bois/Projet_odyssee.git/wiki)