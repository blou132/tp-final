# Maintenance

## 1. Maintenance corrective
Objectif: corriger rapidement un défaut sans régression.

### Procédure
1. Reproduire l'anomalie (scénario + logs).
2. Ouvrir branche `bugfix/<sujet>`.
3. Corriger avec tests associés.
4. Vérifier:
   - `php artisan test`
   - comportement web/API
5. PR + revue + merge.
6. Mettre à jour documentation si impact fonctionnel.

## 2. Maintenance évolutive
Objectif: ajouter une fonctionnalité tout en conservant la stabilité.

### Procédure
1. Décrire le besoin (fonctionnel + technique).
2. Ouvrir branche `feature/<sujet>`.
3. Implémenter en respectant:
   - FormRequest
   - Policy / permissions
   - tests
4. Documenter:
   - README/API
   - impacts DB/migrations
5. PR + validation + merge.

## 3. Gestion base de données
- Migrations versionnées.
- Seeders pour données initiales (rôles, comptes de démo).
- Rollback possible en cas de régression:
  ```bash
  php artisan migrate:rollback
  ```

## 4. Supervision et logs
- Logs applicatifs Laravel pour erreurs runtime.
- Audit métier dans MongoDB (`activity_logs`).
- Contrôle périodique des actions sensibles (création/suppression).

## 5. Checklist post-correction / post-évolution
- Tests unitaires et feature passés.
- Vérification CRUD tickets/paiements.
- Vérification sécurité des accès.
- Vérification endpoints API avancés.
- Documentation à jour.
