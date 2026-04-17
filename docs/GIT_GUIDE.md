# Guide Git / Branches

## Stratégie
- `main`: production stable
- `develop`: intégration continue
- `feature/*`: nouvelles fonctionnalités
- `bugfix/*`: corrections ciblées

## Workflow recommandé
1. Créer une branche depuis `develop`.
2. Développer la fonctionnalité ou correction.
3. Commits atomiques et explicites.
4. Pull request vers `develop`.
5. Revue + tests automatiques.
6. Merge `develop` -> `main` pour livraison.

## Convention de commits
Exemples:
- `feat(tickets): add profanity filter on create/update`
- `fix(api): secure users/{email}/tickets access`
- `docs(readme): add docker startup procedure`

## Bonnes pratiques
- Ne pas commiter `.env`.
- Toujours lancer les tests avant PR.
- Préserver l'historique lisible (commits courts, descriptifs).
