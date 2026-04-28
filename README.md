# 🌦️ Weather App (Laravel + Vue.js + Inertia)

Une application météo moderne, rapide et responsive, construite avec **Laravel**, **Vue.js 3** et **Inertia.js**. Elle utilise l'API **Open-Meteo** pour fournir des prévisions précises sans clé API complexe.

## ✨ Fonctionnalités

- 📍 **Recherche de villes** : Recherchez n'importe quelle ville dans le monde grâce à l'autocomplétion.
- 🕒 **Temps Réel & Prévisions** : Affichage de la météo actuelle, des prévisions horaires et sur 14 jours.
- 🌓 **Astronomie** : Suivi des phases de la lune et des horaires de lever/coucher du soleil.
- 🌡️ **Indicateurs détaillés** : Index UV, humidité, probabilité de pluie, vent (vitesse et direction).
- 💾 **Persistance** :
    - Les villes ajoutées sont sauvegardées côté serveur.
    - La dernière ville consultée est mémorisée localement (`localStorage`) pour s'afficher automatiquement au retour de l'utilisateur.
- 🎨 **Interface Dynamique** :
    - Thème adaptatif (Clair/Sombre).
    - Couleurs de fond changeantes en fonction de la météo et du moment de la journée (jour/nuit).
- 📱 **Expérience Mobile** : Support du "Pull-to-refresh" pour rafraîchir les données manuellement.

## 🚀 Stack Technique

- **Backend** : [Laravel](https://laravel.com)
- **Frontend** : [Vue.js 3](https://vuejs.org/) (Composition API)
- **Lien Backend/Frontend** : [Inertia.js](https://inertiajs.com/)
- **Style** : [Tailwind CSS](https://tailwindcss.com/)
- **Icônes** : [Weather Icons](https://erikflowers.github.io/weather-icons/) & FontAwesome
- **API Météo** : [Open-Meteo](https://open-meteo.com/)

## 🛠️ Installation en local

### Prérequis

- PHP 8.3+
- Composer
- Node.js & npm (ou pnpm)
- SQLite (ou autre base de données supportée par Laravel)

### Étapes

1. **Cloner le projet**
   ```bash
   git clone <votre-url-repo>
   cd native
   ```

2. **Installer les dépendances PHP**
   ```bash
   composer install
   ```

3. **Installer les dépendances JS**
   ```bash
   npm install
   ```

4. **Configurer l'environnement**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Initialiser la base de données** (Si nécessaire selon votre config)
   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```

6. **Lancer le serveur de développement**
   ```bash
   npm run dev
   ```
   *Cette commande lance simultanément le serveur Laravel (`php artisan serve`) et Vite.*

## 🚢 Déploiement

Pour déployer l'application en production :

1. **Optimiser Laravel**
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

2. **Compiler les assets**
   ```bash
   npm run build
   ```

3. **Serveur Web**
   Pointez le document root de votre serveur (Apache/Nginx) vers le dossier `/public` du projet.

## 📂 Structure du projet

- `app/Services` : Contient la logique métier (calculs astronomiques, appels API météo).
- `app/Http/Controllers` : Gère les requêtes et les réponses Inertia.
- `resources/js/Pages` : Composants Vue principaux.
- `resources/js/components` : Éléments d'interface réutilisables.
- `storage/app/cities.json` : Stockage des villes personnalisées par l'utilisateur.

## 📝 License

Ce projet est sous licence MIT.
