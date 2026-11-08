# Bloc 7 — Partie C : MCD du socle

Ce modèle décrit les tables SQL de la partie C, notamment les tranches mensuelles attachées au contrat et le portail salarié. L'acquisition et l'allocation FIFO sont exécutées par les services; le schéma conserve les mouvements nécessaires à leur traçabilité.

## Diagramme

```mermaid
erDiagram
    EMPLOYE ||--o{ EMPLOYE_ROLE : possede
    ROLE ||--o{ EMPLOYE_ROLE : attribue
    EMPLOYE ||--o{ CONTRAT : signe
    EMPLOYE ||--o{ CONTRAT : encadre
    CONTRAT ||--o{ DROIT_CONGE : genere
    EMPLOYE ||--o{ DROIT_CONGE : recoit
    TYPE_CONGE ||--o{ DROIT_CONGE : qualifie
    CONTRAT ||--o{ SOLDE_CONGE : porte
    TYPE_CONGE ||--o{ SOLDE_CONGE : resume
    EMPLOYE ||--o{ DEMANDE_CONGE : soumet
    CONTRAT ||--o{ DEMANDE_CONGE : rattache
    TYPE_CONGE ||--o{ DEMANDE_CONGE : categorise
    DEMANDE_CONGE ||--o{ VALIDATION_DEMANDE_CONGE : historise
    EMPLOYE ||--o{ VALIDATION_DEMANDE_CONGE : decide
    DEMANDE_CONGE ||--o{ ALLOCATION_DROIT_CONGE : reserve
    DROIT_CONGE ||--o{ ALLOCATION_DROIT_CONGE : impute
    DEMANDE_CONGE ||--o{ NOTIFICATION_EMPLOYE : declenche
    EMPLOYE ||--o{ NOTIFICATION_EMPLOYE : recoit
    DEMANDE_CONGE ||--o{ ANNULATION_DEMANDE_CONGE : historise
    EMPLOYE ||--o{ ANNULATION_DEMANDE_CONGE : retire
    DEMANDE_CONGE ||--o{ JUSTIFICATIF_CONGE : documente
    DEMANDE_CONGE ||--o| ABSENCE : alimente
    CONTRAT ||--o{ ABSENCE : rattache
    TYPE_CONGE ||--o{ ABSENCE : categorise

    EMPLOYE {
        int id PK
        int manager_id FK
        string email
        string mot_de_passe
    }
    EMPLOYE_ROLE {
        int employe_id PK,FK
        int role_id PK,FK
    }
    CONTRAT {
        int id PK
        int employe_id FK
        date date_debut
        date date_fin
        string statut
    }
    DROIT_CONGE {
        int id PK
        int employe_id FK
        int contrat_id FK
        int type_conge_id FK
        date date_acquisition
        date date_expiration
        decimal quantite
        string nature
    }
    SOLDE_CONGE {
        int id PK
        int employe_id FK
        int contrat_id FK
        int type_conge_id FK
        decimal quantite_acquise
        decimal quantite_consommee
        decimal quantite_reservee
        decimal quantite_disponible
    }
    DEMANDE_CONGE {
        int id PK
        int employe_id FK
        int contrat_id FK
        int type_conge_id FK
        date debut
        date fin
        decimal duree_demandee
        string unite_decompte
        string statut
    }
    VALIDATION_DEMANDE_CONGE {
        int id PK
        int demande_conge_id FK
        int validateur_employe_id FK
        string etape
        string decision
        string commentaire
        datetime decide_le
    }
    ALLOCATION_DROIT_CONGE {
        int id PK
        int demande_conge_id FK
        int droit_conge_id FK
        int numero_tentative
        decimal quantite
        string statut
    }
    NOTIFICATION_EMPLOYE {
        int id PK
        int employe_id FK
        int demande_conge_id FK
        string code
        datetime cree_le
        datetime lu_le
    }
    ANNULATION_DEMANDE_CONGE {
        int id PK
        int demande_conge_id FK
        int annule_par_employe_id FK
        string motif
        datetime annule_le
    }
    TYPE_CONGE {
        int id PK
        string code UK
        string unite_decompte
    }
    ABSENCE {
        int id PK
        int employe_id FK
        int contrat_id FK
        int type_conge_id FK
        int demande_conge_id FK
    }
    JOUR_FERIE {
        date date_ferie PK
        string libelle
        boolean actif
    }
```

## Entités et règles de liaison

| Entité | Rôle / contrainte |
|---|---|
| `employe`, `role`, `employe_role` | Le compte de connexion reste lié à `employe.id` via les identifiants déjà présents. La relation plusieurs-à-plusieurs permet d'ajouter Salarié à un Manager ou RH sans remplacer ses rôles. |
| `contrat` | Chaque droit et demande est rattaché au contrat concerné. Les clés étrangères composites garantissent que le contrat appartient au même salarié. |
| `droit_conge` | Une ligne représente une tranche ou régularisation. Une acquisition mensuelle vaut 2,5 jours ouvrés et expire trois ans après son acquisition. Les acquisitions futures ne sont pas créées par anticipation. |
| `solde_conge` | Synthèse par contrat et type. Le disponible est acquis moins consommé moins réservé. Un nouveau contrat possède un solde distinct initialement nul ; aucun report entre contrats. |
| `demande_conge` | Demande faite par l'employé connecté pour son propre dossier, sur son contrat courant. L'unité persistée est `jours_ouvres`. |
| `validation_demande_conge` | Historique de décisions. Une demande est routée au manager direct, ou à la RH si le salarié n'a pas de manager ; une seule décision métier est attendue pour chaque soumission. |
| `allocation_droit_conge` | Allocation d'une demande vers les tranches de droits, conservant quantités réservées, consommées ou libérées pour appliquer le FIFO et tracer les corrections/retraits. |
| `notification_employe` | Notification applicative liée au destinataire et, si pertinent, à la demande. |
| `annulation_demande_conge` | Audit des retraits par le salarié avant approbation et des annulations RH après approbation. |
| `absence` | Donnée de suivi raccordée à la demande approuvée et au même contrat. |
| `jour_ferie` | Calendrier servant au calcul des jours ouvrés, à alimenter selon le calendrier applicable. |

## Contraintes métier exécutées par les services

- Créditer 2,5 à chaque anniversaire mensuel complet depuis le début du contrat ; ne pas proratiser un mois incomplet.
- Expirer une tranche à sa date d'expiration, sans suspension liée au refus d'une demande, et allouer les consommations annuelles en FIFO parmi les tranches encore valides.
- Le contrôle de demande utilise uniquement le solde acquis à l'instant de soumission ; les congés maladie et exceptionnels ne déduisent pas le solde annuel.
- Une demande retournée pour correction, resoumise, refusée ou retirée doit préserver l'historique. Toute réservation libérée doit être répercutée au solde dans la même transaction.
- L'unité `jours_ouvres` est imposée dans le SQL ; elle suppose une définition cohérente des jours fériés et du calendrier.

## Limites du modèle

La génération planifiée doit exécuter `php spark conges:acquerir` quotidiennement. Les informations de compte salarié réutilisent `employe.email` et `employe.mot_de_passe`; la RH peut activer ou réinitialiser le compte depuis l'écran dédié. L'email de notification, la récupération autonome du mot de passe, les règles de conservation des justificatifs et la modification d'un congé déjà approuvé restent hors périmètre actuel.
