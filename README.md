# Projet Groupe 1 — Application Laravel

**Présentation**
- **But :** Application développée dans le cadre universitaire par un groupe de 9 personnes.
- **Contexte :** Travail réalisé sur 3,5 jours de développement intensif + 1 jour dédié à la création de la base de données et à la réalisation de la maquette.
- **Stack :** Laravel (PHP), Composer, Node.js / Vite, MySQL (ou équivalent).

**Lancer le projet en localhost**
- **Prérequis :** PHP 8+, Composer, Node.js (16+), npm/yarn, une base de données (MySQL/MariaDB/Postgres).
- **Cloner le dépôt :**

```bash
git clone <url-du-depot>
cd groupe1/laravel
```

- **Installer les dépendances PHP :**

```bash
composer install
```

- **Installer les dépendances JS et builder :**

```bash
npm install
npm run dev
# ou pour build production
# npm run build
```

- **Configurer l'environnement :**

Copier `.env.example` en `.env` et adapter les variables de la base de données (`DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

- **Générer la clé d'application :**

```bash
php artisan key:generate
```

- **Migrer la base et (optionnel) seed :**

```bash
php artisan migrate
php artisan db:seed  # si des seeders fournis
```

- **Lancer le serveur de développement Laravel :**

```bash
php artisan serve
```

L'application sera accessible par défaut sur `http://127.0.0.1:8000`.

**Arborescence principale (extrait)**
- **laravel/** : code applicatif Laravel
  - **app/** : contrôleurs, modèles, notifications, providers
    - `Http/Controllers/` : contrôleurs (ex : `AuthController.php`, `RaceController.php`)
    - `Models/` : modèles Eloquent (ex : `User.php`, `Participate.php`)
  - **bootstrap/** : bootstrap de l'application
  - **config/** : fichiers de configuration
  - **database/**
    - `factories/` : factories
    - `seeders/` : seeders
  - **public/** : point d'entrée (`index.php`), assets
  - **resources/**
    - `views/` : templates Blade
    - `js/` : scripts front (ex : `app.js`, `inscription.js`)
    - `css/` : styles
  - **routes/** : `web.php`, `console.php`
  - **tests/** : tests unitaires et fonctionnels

(Le dépôt contient d'autres fichiers et dossiers — ceci est un extrait utile pour se repérer rapidement.)

**Description du projet**
- **Equipe :** 9 membres (répartition variable selon les tâches).
- **Durée :** 3,5 jours de développement en sprint intensif + 1 journée dédiée à la conception de la base de données et à la maquette UX/UI.
- **Objectifs :** Produire une application fonctionnelle, avec authentification, gestion d'utilisateurs/équipes/inscriptions et interfaces front basiques, le tout empaqueté avec un système de tests minimal.
- **Livrables :** code source, migrations, seeders (si présents), maquette/diagramme de la base.

**Notes / Conseils**
- Vérifier les versions PHP/Node si des erreurs apparaissent.
- Pour importer une base de données fournie, adapter les credentials dans `.env` puis exécuter les migrations et les seeders.
- Si `npm run dev` utilise Vite, s'assurer que le serveur Vite est lancé pour le rechargement à chaud.

---

Si tu veux, je peux :
- Mettre ce contenu dans `groupe1/README.md` (remplacer l'actuel).
- Générer une version FR/EN ou ajouter des sections pour la contribution et la liste des membres.
