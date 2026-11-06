# Contenu de chaque écran

## Écran 1 — Répertoire des salariés

**URL :** `/bloc5/dossiers`

- Liste des salariés déjà créés dans le Bloc 3.
- Informations affichées :
  - Matricule.
  - Nom et prénom.
  - Photo.
  - Département.
  - Poste actuel.
  - Date d'embauche.
  - Statut du salarié.
- Recherche par nom, prénom ou matricule.
- Filtrage par département et statut.
- Pagination :
  - 10 salariés par page.
  - Boutons Précédent et Suivant.
  - Numéros des pages.
  - Nombre total de résultats.
- Actions :
  - Consulter le dossier.
  - Exporter la liste en PDF.

## Écran 2 — Consulter le dossier salarié

**URL :** `/bloc5/dossier`

- Identification du salarié :
  - Matricule.
  - Nom et prénom.
  - Photo.
- Informations générales :
  - Date d'embauche.
  - Département.
  - Poste actuel.
  - Statut.
- Présentation des différentes sections :
  - Identité et coordonnées.
  - Situation personnelle.
  - Informations professionnelles.
  - Documents administratifs.
  - Contrats.
  - Affectations.
  - Rémunérations.
  - Historique de carrière.
- Actions :
  - Modifier les informations.
  - Consulter les documents.
  - Consulter la carrière.
  - Exporter le dossier en PDF.

## Écran 3 — Informations personnelles

**URL :** `/bloc5/dossier`

Cet écran permet de consulter et de modifier les informations personnelles du salarié.

### Identité

- Nom.
- Prénom.
- Date de naissance.
- Numéro CNI.
- Date de délivrance de la CNI.
- Lieu de délivrance de la CNI.
- Photo.

### Coordonnées

- Email.
- Téléphone.
- Adresse.

### Situation personnelle

- Situation familiale.

### Informations professionnelles

- Matricule.
- Date d'embauche.
- Département.
- Rôle.
- Statut.

### Actions

- Modifier.
- Enregistrer.
- Annuler.

## Écran 4 — Documents administratifs

**URL :** `/bloc5/documents`

- Liste des documents associés au salarié.
- Informations affichées :
  - Nom du document.
  - Type de document.
  - Date d'émission.
  - Date d'expiration.
  - Date d'ajout.
- Types de documents :
  - Pièce d'identité.
  - Contrat signé.
  - Autres documents administratifs.

### Actions

- Ajouter un document.
- Consulter / Télécharger.
- Modifier les informations.
- Supprimer.
- Filtrer par type de document.
- Pagination.
- Exporter la liste des documents en PDF.

### Gestion des dates

- Identifier les documents expirés.
- Signaler les documents bientôt expirés.
- Afficher les documents sans date d'expiration.

## Écran 5 — Historique de carrière

**URL :** `/bloc5/carriere`

Cet écran permet de suivre l'évolution professionnelle du salarié dans l'entreprise.

### Historique des contrats

- Type de contrat.
- Date de début.
- Date de fin.
- Salaire associé.
- Statut du contrat.

### Historique des affectations

- Département.
- Poste occupé.
- Date de début.
- Date de fin.

### Historique des rémunérations

- Ancien salaire.
- Nouveau salaire.
- Date d'effet.
- Motif du changement.

### Actions

- Consulter les détails.
- Filtrer par période.
- Afficher l'évolution chronologique.
- Exporter l'historique en PDF.

# Statuts principaux

## Statut du salarié

- Actif.
- Inactif.

## Statut des documents

- Valide.
- Bientôt expiré.
- Expiré.
- Sans date d'expiration.

> Ces statuts peuvent être calculés automatiquement à partir des dates de validité.

# Règles principales

- Le Bloc 5 ne crée pas de nouveau salarié : les salariés proviennent du Bloc 3.
- Chaque salarié possède un matricule unique.
- Les informations personnelles peuvent être modifiées par les utilisateurs autorisés.
- Les informations professionnelles doivent rester cohérentes avec les contrats et affectations enregistrés dans les autres blocs.
- Un document doit être associé à un salarié.
- Un document de type `contrat_signe` peut être associé à un contrat.
- Les fichiers doivent être stockés dans le système de fichiers ; la base de données conserve leur chemin et leurs métadonnées.
- L'historique des contrats provient de la table `contrat`.
- L'historique des affectations provient de la table `affectation`.
- L'historique des rémunérations provient de la table `historique_salaire`.
- L'export PDF doit respecter les autorisations d'accès.
- La pagination doit être effectuée côté serveur.

# Tables 

| Table | Rôle |
|---|---|
| `employe` | Informations personnelles et professionnelles du salarié. |
| `departement` | Département du salarié. |
| `role` | Rôle du salarié. |
| `contrat` | Historique des contrats. |
| `type_contrat` | Types de contrats. |
| `affectation` | Historique des postes occupés. |
| `document` | Documents administratifs. |
| `historique_salaire` | Historique des rémunérations. |