# Partie D — schéma PostgreSQL et intégration

## Périmètre

La partie D fournit quatre écrans dynamiques :

- `/bloc6/anomalies`
- `/bloc6/temps-travaille`
- `/bloc7/absences`
- `/bloc7/types-conges`

Les écritures sont réservées aux rôles RH et Administrateur, protégées par CSRF. Aucun journal d’audit global n’est ajouté ; seules les traces métier D sont conservées.

## Tables réutilisées

| Table | Provenance | Colonnes utilisées par D |
|---|---|---|
| `employe` | socle partagé | `id`, `matricule`, `nom`, `prenom`, `departement_id`, `actif` |
| `departement` | socle partagé | `id`, `nom_departement` |
| `planning_travail` | partie B | `id`, `employe_id`, `date_travail`, `heure_debut_prevue`, `heure_fin_prevue`, `pause_minutes_prevue`, `heures_prevues`, `est_jour_repos` |
| `pointage` | partie B | `id`, `employe_id`, `date_pointage`, `heure_entree`, `heure_sortie`, `pause_minutes`, `temps_travaille_heures`, `retard_minutes`, `heures_supplementaires`, `statut`, `motif_correction` |
| `demande_conge` | partie C | liaison facultative, uniquement si `id`, `employe_id`, `date_debut`, `date_fin` et `statut` sont compatibles |

La partie D ne recrée pas les tables de B. Les services utilisent directement leurs noms et champs confirmés. Les routes et contrôleurs de B restent séparés.

## Tables créées par D

### `type_conge`

Référentiel partagé avec C : code unique, libellé, description, activation, justificatif obligatoire et mode de décompte configurable. Un type utilisé n’est jamais supprimé par l’application ; il est désactivé.

### `absence`

Registre des absences : salarié, période, type facultatif, demande approuvée facultative, motif, statut et métadonnées du justificatif. Les fichiers sont stockés dans `writable/uploads/absences`, hors du webroot.

### `absence_historique`

Historique local des créations et modifications d’absences avec auteur et date.

### `anomalie_pointage`

Anomalies détectées depuis le planning et les pointages. La contrainte unique `(employe_id, date_anomalie, type)` empêche les doublons. Les statuts sont `A_EXAMINER`, `EN_COURS`, `REGULARISEE` et `IGNOREE`.

### `anomalie_pointage_historique`

Historique des changements de statut, motifs et auteurs. Une régularisation ne modifie pas silencieusement le pointage.

## Clés étrangères

- `absence.employe_id` → `employe.id`
- `absence.type_conge_id` → `type_conge.id`
- `absence.demande_conge_id` → `demande_conge.id`, ajoutée conditionnellement
- `anomalie_pointage.employe_id` → `employe.id`
- `anomalie_pointage.planning_id` → `planning_travail.id`, ajoutée conditionnellement
- `anomalie_pointage.pointage_id` → `pointage.id`, ajoutée conditionnellement
- tables historiques → entité D correspondante et auteur `employe.id`

Les clés vers B et C sont créées `NOT VALID` afin de ne pas casser d’anciennes données partagées ; elles s’appliquent immédiatement aux nouvelles écritures. Une validation globale peut être faite ultérieurement après nettoyage coordonné.

## Sécurité de migration

- `up()` vérifie la compatibilité des tables D préexistantes avant toute création.
- les créations et index sont idempotents (`IF NOT EXISTS`).
- `down()` refuse toute suppression automatique : les tables deviennent partagées avec C et ne doivent pas être perdues lors d’un rollback ordinaire.
- aucune migration destructive ne doit être lancée sur la base partagée.

## Ordre d’intégration recommandé

1. Fusionner D dans `main` après validation de sa PR.
2. Laisser B fusionner sa propre branche ; résoudre son éventuel conflit préexistant sur `EmployeModel.php` sans supprimer ses écrans.
3. Le menu D est injecté par `Config/Menu/partieD.php`, donc les entrées Anomalies et Temps travaillé restent présentes même si le fichier menu de B ne contient que ses trois écrans.
4. Exécuter la migration D une seule fois lorsque les tables partagées nécessaires existent.
5. Valider ultérieurement les contraintes `NOT VALID` après audit des données historiques.

Si B ou C crée ses tables après l'exécution de la migration D, exécuter ensuite
`database/partie_d/complete_foreign_keys.sql`. Ce script idempotent ajoute uniquement
les clés devenues possibles et ne supprime ni ne réécrit aucune donnée.

## Tests

- `php tests/PartieDWorkflowRunner.php` : règles pures.
- `php tests/PostgreSQLPartieDRunner.php` : inspection publique en lecture seule, migration et scénarios métier dans un schéma PostgreSQL temporaire automatiquement supprimé.
- `php spark routes` : priorité des routes explicites et filtres.
- vérification syntaxique PHP et `git diff --check`.
