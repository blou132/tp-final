# Cahier des Charges Synthétique

## Contexte
Réalisation d'un projet final BTS en une semaine autour d'une application Laravel CRUD, avec contraintes techniques imposées (Vue, Spatie, MongoDB, i18n, sécurité, Docker, CI/CD).

## Besoin métier
Permettre à un utilisateur authentifié de gérer:
- ses tickets,
- ses paiements,
avec une séparation claire des droits entre utilisateur standard et administrateur.

## Contraintes fonctionnelles
- CRUD Tickets
- CRUD Payments
- Relation `User -> Tickets`, `User -> Payments`
- Filtrage anti-profanité sur tickets
- API avancée tickets (`open-tickets`, `closed-tickets`, `users/{email}/tickets`, `stats`)

## Contraintes techniques
- Laravel récent stable
- Frontend Vue.js
- MySQL principal
- MongoDB pour audit
- Spatie Permission
- Tests automatisés
- Docker + docker-compose
- CI/CD (Bitbucket Pipelines)

## Exigences de qualité
- Validation stricte
- Policies/permissions
- Code maintenable, lisible, PSR
- Documentation technique exploitable
- Projet exécutable et testable

## Livrables
- Code source complet
- README d'exploitation
- Schémas UML
- Guide Git/branches
- Procédures de maintenance corrective et évolutive
- Docker + pipeline CI/CD
