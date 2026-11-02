# Squelettes de développement — Blocs 5 à 7

## Portée

Ce socle traduit les fonctionnalités listées dans `Conception-Logiciel-RH-2.md` en navigation et écrans de démonstration. Il ne contient aucune règle métier et n’effectue aucune lecture ou écriture dans la base. Les tableaux et indicateurs affichés sont fictifs ; les champs et boutons sont désactivés.

Les Blocs 1 à 4 n’ont pas été reconstruits. Les nouveaux blocs suivent la séparation déjà utilisée dans le dépôt : routes par bloc, contrôleur, service de présentation, modèle et vue commune au rendu des maquettes.

## Écrans disponibles

| Bloc | URL | Fonctionnalités représentées |
|---|---|---|
| 5 — Dossier salarié | `/bloc5/dossiers` | Répertoire et accès au référentiel salarié |
| 5 — Dossier salarié | `/bloc5/dossier` | Identité, coordonnées, situation, contrats, affectations, salaire et carrière |
| 5 — Dossier salarié | `/bloc5/documents` | Documents administratifs et dates de validité |
| 5 — Dossier salarié | `/bloc5/carriere` | Historique des postes, contrats et rémunérations |
| 5 — Dossier salarié | `/bloc5/audit` | Maquette des habilitations, confidentialité et journal d’audit |
| 6 — Temps et pointage | `/bloc6/horaires` | Horaires et régimes de travail |
| 6 — Temps et pointage | `/bloc6/planning` | Planning par salarié et période |
| 6 — Temps et pointage | `/bloc6/pointages` | Entrées, sorties, retards et heures supplémentaires |
| 6 — Temps et pointage | `/bloc6/anomalies` | Écarts de pointage à examiner |
| 6 — Temps et pointage | `/bloc6/temps-travaille` | Temps réellement travaillé et futur transfert vers absences/paie |
| 7 — Congés et absences | `/bloc7/demandes` | Demandes de congé et saisie des pièces éventuelles |
| 7 — Congés et absences | `/bloc7/validations` | Validation Manager/RH et contrôles de solde/chevauchement à définir |
| 7 — Congés et absences | `/bloc7/soldes` | Droits acquis, consommés et disponibles |
| 7 — Congés et absences | `/bloc7/absences` | Absences et justificatifs |
| 7 — Congés et absences | `/bloc7/types-conges` | Types de congé et paramètres à définir |

## Structure de code

- `app/Config/Routes/bloc5.php` à `bloc7.php` : routes GET des écrans, protégées par la session.
- `app/Config/Menu/bloc5.php` à `bloc7.php` : navigation latérale.
- `app/Controllers/Bloc5` à `Bloc7` : contrôleurs d’affichage statique.
- `app/Services/Bloc5` à `Bloc7` : catalogues de pages et de contenu fictif uniquement.
- `app/Models/Bloc5` à `Bloc7` : modèles à compléter lorsque le schéma de données sera décidé.
- `app/Views/shared/module_scaffold.php` : présentation commune des maquettes.

Les noms de tables des modèles ajoutés pour les nouveaux objets sont des propositions de travail, pas un schéma validé. Les modèles existants `EmployeModel`, `ContratModel`, `AffectationModel` et `DocumentModel` restent disponibles pour les entités déjà présentes.

## À définir avant le développement métier

- Schéma, relations, contraintes et migrations des Blocs 5 à 7.
- Rôles et habilitations, en particulier pour les salaires, dossiers, pointages, justificatifs et décisions de congé.
- Règles de validité des documents, acquisition et consommation des congés, calcul du temps, heures supplémentaires et traitement des anomalies.
- Échanges avec les blocs absences et paie, ainsi que les règles de synchronisation du planning.
