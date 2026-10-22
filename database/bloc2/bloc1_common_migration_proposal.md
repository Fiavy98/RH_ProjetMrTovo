# Correction des migrations communes du Bloc 1

La correction ci-dessous a été appliquée après accord du responsable du Bloc 1. Elle ne modifie aucune règle de validation RH, Finance ou Direction et ne transforme aucune donnée métier.

## `UpdateDemandePoste.php`

La migration initiale du 31 juillet crée déjà `demande.intitule` et `demande.nombre_postes`, tandis que `poste.nombre_postes` n'existe pas. La migration du 22 octobre échoue donc sur une base neuve.

Correction appliquée, compatible avec une base neuve et une base ancienne :

1. vérifier `tableExists('demande')` et `tableExists('poste')` ;
2. ajouter chaque colonne de `demande` uniquement si `fieldExists()` renvoie faux ;
3. supprimer `poste.nombre_postes` uniquement si elle existe ;
4. supprimer uniquement un ancien index/contrainte `UNIQUE` portant exactement sur `poste.demande_id`, tout en conservant l'index normal et la clé étrangère ;
5. conserver un `down()` non destructif, car les colonnes sont désormais possédées par le schéma initial.

Sur une base neuve issue de la migration initiale actuelle, `up()` devient donc volontairement sans effet.

## `AddTypeContratToDemande.php`

`type_contrat` n'était pas créée par la migration initiale observée. La migration crée désormais ce référentiel partagé uniquement lorsqu'il est absent, avec les colonnes attendues par `TypeContratModel`, puis vérifie :

- l'existence de `type_contrat` et de sa colonne `id` ;
- la compatibilité du type de `demande.type_contrat_id` avec `type_contrat.id` ;
- la compatibilité de la colonne existante, le cas échéant ;
- l'absence préalable de la relation avant de créer `fk_demande_type_contrat`.

Une base existante qui possède déjà la table, la colonne ou la relation n'est pas modifiée une seconde fois. Une structure existante incomplète ou dont les types d'identifiants sont incompatibles échoue avec un message explicite. Le rollback ne supprime jamais `type_contrat`, puisqu'elle est partagée avec les Blocs 3 et 4.

## Vérification automatisée

`php tests/database/CommonMigrationCompatibilityRunner.php` couvre un schéma neuf et un ancien schéma, ainsi que la répétition de chaque migration pour vérifier son idempotence.

## Point à arbitrer

Les migrations et services consultés ne définissent aucun code officiel pour l'état administratif d'un `poste`. Il faut fixer ce vocabulaire avant de corriger les lignes de démonstration portant actuellement un statut `FINANCE_*`.
