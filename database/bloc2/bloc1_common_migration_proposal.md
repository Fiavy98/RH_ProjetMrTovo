# Proposition minimale pour les migrations communes du Bloc 1

Ce document est une proposition destinée au responsable du Bloc 1. Aucun fichier du Bloc 1 n'est modifié par le Bloc 2.

## `UpdateDemandePoste.php`

La migration initiale du 31 juillet crée déjà `demande.intitule` et `demande.nombre_postes`, tandis que `poste.nombre_postes` n'existe pas. La migration du 22 octobre échoue donc sur une base neuve.

Correction compatible avec une base neuve et une base ancienne :

1. vérifier `tableExists('demande')` et `tableExists('poste')` ;
2. ajouter chaque colonne de `demande` uniquement si `fieldExists()` renvoie faux ;
3. supprimer `poste.nombre_postes` uniquement si elle existe ;
4. ne supprimer l'ancien index/contrainte sur `poste.demande_id` qu'après avoir retrouvé son nom réel dans PostgreSQL ;
5. rendre `down()` symétrique uniquement pour les objets effectivement créés par cette migration, ou le laisser non destructif si cette migration a déjà circulé entre environnements.

Sur une base neuve issue de la migration initiale actuelle, `up()` devient donc volontairement sans effet.

## `AddTypeContratToDemande.php`

`type_contrat` n'est pas créée par la migration initiale observée. La correction minimale est de placer/créer la migration propriétaire de `type_contrat` avant celle-ci, puis de vérifier :

- l'existence de `type_contrat` et de sa colonne `id` ;
- la compatibilité du type de `demande.type_contrat_id` avec `type_contrat.id` ;
- l'absence préalable de la colonne et de la contrainte `fk_demande_type_contrat`.

Une base existante qui possède déjà la colonne ou la contrainte ne doit pas être modifiée une seconde fois. Une base sans `type_contrat` doit échouer avec un message explicite, jamais créer silencieusement une table métier dont la responsabilité appartient à un autre bloc.

## Point à arbitrer

Les migrations et services consultés ne définissent aucun code officiel pour l'état administratif d'un `poste`. Il faut fixer ce vocabulaire avant de corriger les lignes de démonstration portant actuellement un statut `FINANCE_*`.
