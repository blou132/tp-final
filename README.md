# TP Final BTS - Laravel CRUD Tickets & Payments

Projet final BTS orienté **CRUD Laravel + Vue.js**, avec sécurité, API avancée, rôles/permissions, i18n FR/EN, audit MongoDB, Docker et CI/CD Bitbucket.

## 1. Objectifs pédagogiques couverts
- Application web CRUD complète avec Laravel.
- Frontend dynamique avec Vue.js (Inertia).
- Authentification utilisateur.
- Gestion relationnelle `User -> Tickets` et `User -> Payments`.
- Sécurité applicative (validation, policies, autorisations, mass assignment maîtrisé).
- Internationalisation FR/EN.
- Profanity filter sur tickets.
- Intégration Spatie (rôles/permissions).
- Intégration MongoDB cohérente (journal d'activité/audit).
- API avancée Tickets + tests.
- Conteneurisation Docker + pipeline Bitbucket.

## 2. Stack technique
- **Backend**: Laravel 13, PHP 8.3
- **Frontend**: Vue 3 + Inertia + Vite
- **DB principale**: MySQL
- **DB secondaire**: MongoDB (audit logs)
- **Auth API**: Sanctum
- **Permissions**: `spatie/laravel-permission`
- **Tests**: PHPUnit (unit + feature + API)
- **DevOps**: Docker, Docker Compose, Bitbucket Pipelines

## 3. Modèle métier imposé
- `User` (modèle Laravel par défaut)
- `Ticket(title, description, status, user_id)` appartient à `User`
- `Payment(amount, status, user_id)` appartient à `User`

Statuts:
- Ticket: `open`, `in_progress`, `closed`
- Payment: `pending`, `paid`, `failed`

## 4. Fonctionnalités implémentées
- Authentification (Breeze/Inertia).
- Dashboard avec statistiques Tickets/Paiements.
- CRUD Tickets (web) avec filtres.
- CRUD Payments (web) avec filtres.
- Profanity filter sur `title` + `description` de Ticket.
- Gestion de rôles:
  - `admin`
  - `user`
- Permissions fines Spatie pour Tickets/Paiements/API.
- API avancée sécurisée:
  - `GET /api/open-tickets`
  - `GET /api/closed-tickets`
  - `GET /api/users/{email}/tickets`
  - `GET /api/stats`
- i18n FR/EN avec changement de langue côté interface.
- Audit MongoDB des actions principales:
  - `ticket.created`, `ticket.updated`, `ticket.deleted`
  - `payment.created`, `payment.updated`, `payment.deleted`

## 5. Architecture applicative
- **Models**: `User`, `Ticket`, `Payment`, `ActivityLog` (MongoDB).
- **Enums**: `TicketStatus`, `PaymentStatus`.
- **Requests**: validation dédiée pour create/update Ticket/Payment.
- **Policies**: `TicketPolicy`, `PaymentPolicy`.
- **Services**:
  - `ProfanityFilterService`
  - `ActivityLogService`
- **Controllers**:
  - Web: Dashboard, Tickets, Payments, Locale, Auth/Profile
  - API: `TicketApiController`
- **Frontend Vue**:
  - pages Dashboard/Tickets/Payments
  - layout authentifié + i18n
- **Routes**:
  - `routes/web.php`
  - `routes/api.php`

## 6. Intégration MongoDB (usage cohérent)
MongoDB n'est pas utilisé comme stockage principal. Il sert à l'**audit applicatif**:
- journalisation des actions métier,
- traçabilité des changements,
- base adaptée à des événements semi-structurés.

Collection utilisée: `activity_logs`.

## 7. Sécurité
- Form Requests strictes.
- Policies + rôles/permissions Spatie.
- Restriction des données par ownership (user) ou rôle admin.
- Mass assignment sécurisé via `$fillable`.
- API protégée par `auth:sanctum` + contrôles d'autorisation.
- Messages flash + erreurs gérées proprement.

## 8. Installation locale (sans Docker)
### Prérequis
- PHP 8.3+
- Composer
- Node.js 20+ (Vite 8)
- MySQL 8+
- MongoDB 7/8+
- Extension PHP `mongodb`

### Étapes
```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Application web: `http://127.0.0.1:8000`

### Identifiants seed
- Admin: `admin@example.com` / `password`
- User: `user@example.com` / `password`

## 9. Installation Docker
### Poste vierge (Docker non installe)
```bash
./bootstrap.sh
```

### Commande unique (recommandee)
```bash
./start.sh
```

`start.sh` detecte automatiquement si Docker doit etre lance avec `sudo`.
Le script relance l'environnement proprement (reset des conteneurs/volumes du projet) puis reapplique les migrations/seed.

### Lancer les conteneurs
```bash
docker compose up -d --build
```

### Initialiser l'application
```bash
docker compose exec app cp .env.example .env
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Application web: `http://localhost:8000`

Services:
- MySQL: `localhost:3307`
- MongoDB: `localhost:27017`

## 10. Tests
```bash
php artisan test
```

Couverture incluse:
- unit tests (`ProfanityFilterService`),
- feature tests CRUD/auth/profile,
- API tests endpoints avancés.

## 11. API avancée Tickets
Toutes les routes ci-dessous nécessitent un utilisateur authentifié Sanctum.

### 11.1 Open tickets
- `GET /api/open-tickets`
- Retourne les tickets `open` visibles par l'utilisateur.

### 11.2 Closed tickets
- `GET /api/closed-tickets`
- Retourne les tickets `closed` visibles par l'utilisateur.

### 11.3 Tickets d'un utilisateur par email
- `GET /api/users/{email}/tickets`
- Admin: peut consulter n'importe quel email.
- User: limité à son propre email.

### 11.4 Stats
- `GET /api/stats`
- Retourne:
  - total,
  - open,
  - in_progress,
  - closed,
  - closed_ratio,
  - `by_user` pour admin.

## 12. CI/CD
Fichier: `bitbucket-pipelines.yml`
- Étape 1: installation dépendances + exécution tests.
- Étape 2: build image Docker.

## 13. Arborescence (principale)
```text
app/
  Enums/
  Http/Controllers/
  Http/Requests/
  Http/Middleware/
  Models/
  Policies/
  Services/
database/
  factories/
  migrations/
  seeders/
resources/js/
  Layouts/
  Pages/
routes/
docker/
docs/
```

## 14. Documentation complémentaire
- Cahier des charges: `docs/CAHIER_DES_CHARGES.md`
- UML: `docs/UML.md`
- Guide Git/branches: `docs/GIT_GUIDE.md`
- Maintenance corrective/évolutive: `docs/MAINTENANCE.md`

## 15. Remarque environnement
Si l'extension `ext-mongodb` n'est pas installée localement, l'audit MongoDB est ignoré côté runtime (safe fallback), mais il est activé dans l'environnement Docker prévu pour le rendu.
