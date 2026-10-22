# Bloc 2 — Recrutement

## Dépendances et coordination avec le Bloc 1

Le module attend les tables partagées `demande`, `validation`, `status` et `employe`. La création et la publication d’une offre exigent simultanément :

- le statut courant `DIRECTION_validee` sur la demande ;
- une ligne de validation portant le même statut.

La branche `origin/Bloc-1` confirme que `RH_validee` est une décision intermédiaire et que `DemandeDirectionService` produit ensuite `DIRECTION_validee`. Les jeux de données arrêtés à `RH_validee` doivent être corrigés par le responsable du Bloc 1 ; le Bloc 2 ne les transforme pas en demandes approuvées.

La branche Bloc 1 doit être fusionnée et migrée avant les migrations Bloc 2. La migration vérifie ses dépendances et les colonnes partagées avant toute création. Les nouveaux identifiants propres au Bloc 2 sont des `BIGINT`; les clés étrangères vers les tables partagées reprennent dynamiquement le type réel de leur identifiant. Les tables déjà existantes ne sont ni recréées ni modifiées.

Deux corrections restent à faire par le responsable du Bloc 1 : `UpdateDemandePoste.php` doit devenir conditionnelle car le schéma initial contient déjà les colonnes visées, et `AddTypeContratToDemande.php` doit être ordonnée après la création de `type_contrat`. La proposition détaillée se trouve dans `database/bloc2/bloc1_common_migration_proposal.md`.

## Entretiens et participants

- Une candidature `PRESELECTIONNEE` passe à `EN_ENTRETIEN` lors de sa première planification.
- Une candidature `EN_ENTRETIEN` peut recevoir d’autres entretiens successifs.
- Un seul entretien peut rester `PLANIFIE` à la fois pour une candidature.
- Après annulation, la candidature reste dans le processus et peut être replanifiée. L’annulation et la nouvelle planification sont historisées.
- Chaque entretien possède des participants explicites dans `entretien_participant`.
- Une évaluation est refusée si l’évaluateur n’est pas participant de l’entretien.
- Une décision finale est refusée tant qu’un entretien reste `PLANIFIE`.
- La candidature `RETENUE` reste la sortie du Bloc 2 vers le Bloc 3 ; aucun salarié n'est créé ici.

La notion d'« entretiens obligatoires » par type (`RH`, `TECHNIQUE`, `FINAL`, etc.) n'est pas définie dans le schéma ou les règles partagées. Le module garantit donc qu'aucun entretien déjà planifié ne reste ouvert, mais l'équipe doit arbitrer si une matrice de types obligatoires doit être ajoutée par poste/offre.

## Limites de sécurité connues

- Le sélecteur de rôle du squelette est une démonstration, pas une authentification.
- Le rôle technique `admin` n’hérite pas des décisions RH. Le menu, les écrans et les mutations du Bloc 2 sont réservés au rôle de démonstration `rh`.
- Tant que l’authentification n’est pas reliée à `employe`, RH saisit les avis au nom des participants affectés. Aucun manager ne reçoit un accès global aux candidatures.
- L’auteur des événements reste `NULL` tant qu’aucun employé authentifié n’est disponible.
- Le filtre CSRF global du squelette est désactivé. Les formulaires émettent les champs CSRF, mais leur validation nécessite une activation coordonnée par l’équipe.

## Lancement

Configurer PostgreSQL dans un fichier `.env` local non versionné, fusionner/migrer le Bloc 1, puis :

```bash
php spark migrate
php spark serve
```

Point d’entrée : `http://localhost:8080/bloc2/dashboard`.

## Tests disponibles

```powershell
php tests/bloc2/RecruitmentWorkflowRunner.php
php spark routes
php -l app/Services/Bloc2/RecruitmentService.php
```

Le premier runner utilise une base SQLite en mémoire et ne modifie pas `writable/base.db`. PHPUnit n’est pas installé dans cette copie.

Un runner PostgreSQL crée un schéma temporaire isolé, exécute les migrations Bloc 2, couvre le workflow complet, puis supprime uniquement ce schéma. Par sécurité, il refuse tout nom de base ne contenant pas `test` ou `demo` :

```powershell
$env:BLOC2_TEST_PG_HOST='127.0.0.1'
$env:BLOC2_TEST_PG_PORT='5432'
$env:BLOC2_TEST_PG_DATABASE='sirh_test'
$env:BLOC2_TEST_PG_USER='sirh_test'
$env:BLOC2_TEST_PG_PASSWORD='mot-de-passe-local'
php tests/bloc2/PostgreSQLWorkflowRunner.php
```

Ne jamais diriger ce runner vers la base partagée. Les variables et le mot de passe restent locaux et ne doivent pas être versionnés.

## Données de démonstration

`database/bloc2/correct_demo_data.sql` corrige uniquement une base dont le nom contient `demo` ou `test`. Il requalifie les demandes 1 à 7 seulement lorsqu'une validation explicitement intitulée « Accord de la Direction » existe, normalise les types d'entretien et déduit les participants historiques des évaluations existantes. Il ne fabrique aucune validation ou évaluation.

Le code officiel du statut administratif d'un `poste` n'étant pas défini dans les migrations consultées, la correction des statuts `FINANCE_*` est volontairement laissée commentée jusqu'à arbitrage de l'équipe.

Les fichiers de référence `db_SIRH_fusion.txt`, `db_SIRH_donnees_test.txt`, `db_teste.txt`, `db_changement.txt` et `demandeModifie.txt` n'étaient pas présents dans l'espace inspecté. `note.md` indique uniquement la configuration SQLite historique et ne constitue pas un schéma PostgreSQL de référence.
