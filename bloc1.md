# Bloc 1

## Formulaire de demande

La page de création est accessible à l'adresse :

```text
/bloc1/demande-form
```

La vue dédiée est `app/Views/bloc1/demande_form.php`. Elle utilise des balises
HTML classiques (`input`, `select`, `textarea` et `button`) et reçoit ses données
directement depuis le contrôleur :

- les départements sont chargés depuis la table `departement` ;
- le demandeur est le premier employé actif de la table `employe` ;
- le bouton est désactivé si un employé, un département ou le statut
  `EN_ATTENTE` manque ;
- le formulaire est protégé par le jeton CSRF.

## Règles de création d'une demande

Une demande contient maintenant :

- `intitule` : intitulé commun du poste, obligatoire, limité à 150 caractères ;
- `nombre_postes` : nombre strictement positif de postes à créer ;
- `motif` : motif et justification obligatoires ;
- `budget_min` et `budget_max` : budgets facultatifs, positifs, avec un minimum
  qui ne peut pas dépasser le maximum ;
- `demandeur_id`, `departement_id` et `status_id`.

Le statut initial utilisé est `RH_attente`. Aucun statut `BROUILLON` n'est
utilisé pour une demande.

Une demande créée par un manager est directement visible dans la file RH. Il
n'y a pas d'étape de vérification hiérarchique ou de soumission intermédiaire.

## Relation demande / poste

Une demande peut maintenant être liée à plusieurs lignes de la table `poste`.
La contrainte `UNIQUE` sur `poste.demande_id` a été supprimée.

Le nombre de postes est porté par `demande.nombre_postes`. Lors de la création
des postes, chaque poste reprend l'intitulé de la demande avec un suffixe pour
le distinguer :

```text
Développeur web_1
Développeur web_2
Développeur web_3
```

La colonne `nombre_postes` a été supprimée de la table `poste`.

## Fichiers concernés

- `app/Controllers/Bloc1/Bloc1Controller.php`
- `app/Services/Bloc1/DemandeFormService.php`
- `app/Views/bloc1/demande_form.php`
- `app/Models/DemandeModel.php`
- `app/Models/PosteModel.php`
- `app/Entities/Demande.php`
- `app/Entities/Poste.php`
- `app/Database/Migrations/2026-07-31-093444_CreateTables.php`
- `app/Database/Migrations/2026-10-22-000001_UpdateDemandePoste.php`
- `app/Database/sql/db_SIRH_fusion.sql`
- `app/Database/sql/db_SIRH_donnees_test.sql`

Si la première migration a déjà été exécutée, appliquer ensuite :

```bash
php spark migrate
```
