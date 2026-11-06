# Bloc 5 — Dossier salarié

## Objectif

Centraliser les informations personnelles et professionnelles des salariés afin de permettre aux RH de consulter, modifier et suivre leur dossier tout au long de leur carrière dans l'entreprise.

## Acteurs

| Acteur | Responsabilités |
| --- | --- |
| RH | Consulte les dossiers, met à jour les informations personnelles et gère les documents administratifs. |
| Salarié | Fournit les informations personnelles et les documents nécessaires à la mise à jour de son dossier. |
| Manager | Peut consulter les informations professionnelles du salarié selon ses autorisations. |
| Administrateur | Gère les utilisateurs, les rôles et les autorisations d'accès. |
| Bloc 3 — Sélection et embauche | Transmet les informations du salarié créé après validation de l'embauche. |
| Bloc 4 — Contrat et onboarding | Transmet les contrats et les informations relatives à l'intégration du salarié. |

## Workflow et écrans associés

```mermaid
flowchart TD
    A["Écran 1 — Répertoire des salariés"] --> B["Écran 2 — Consulter le dossier salarié"]

    B --> C["Écran 3 — Informations personnelles"]
    B --> D["Écran 4 — Documents administratifs"]
    B --> E["Écran 5 — Historique de carrière"]

    C --> F["Modifier les informations"]
    F --> G["Enregistrer les modifications"]
    G --> B

    D --> H["Ajouter / Modifier / Télécharger un document"]
    H --> I["Enregistrer le document"]
    I --> D

    E --> J["Consulter les contrats, affectations et salaires"]
    J --> K["Exporter l'historique en PDF"]

    B --> L["Exporter le dossier salarié en PDF"]
    A --> M["Exporter la liste des salariés en PDF"]