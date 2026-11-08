# Rôles et accès aux pages du SIRH

## Portée

Ce document décrit les pages et les rôles autorisés dans l’application. Les règles des Blocs 2, 3 et 4 sont contrôlées côté serveur par un filtre de routes et les menus sont alignés sur ces règles. Le Bloc 1 est conservé tel quel.

Rôles considérés : **Manager**, **RH**, **Finance**, **Direction**, **Administrateur** et **Salarié** (portail Bloc 7 prévu). Dans le cadrage V1 initial, le candidat et le salarié n’accédaient pas à l’application ; le portail salarié constitue une évolution décidée pour le Bloc 7C.

## Socle de l’application

| Page | Rôle actuellement appliqué | Rôles recommandés |
|---|---|---|
| Accueil (`/`) | Tout utilisateur connecté | Manager, RH, Finance, Direction, Administrateur |
| Contrôle de la base (`/dev/db`, hors production) | Tout utilisateur connecté | Administrateur seulement ; conserver l’accès limité au développement |
| Sélection d’utilisateur (`/auth`) | Aucun rôle avant connexion | Aucun : page de connexion/sélection. La sélection actuelle sans mot de passe reste un mécanisme de démonstration. |

## Bloc 1 — Besoins

| Page | Rôle actuellement contrôlé côté serveur | Rôles recommandés |
|---|---|---|
| Vue des demandes (`/bloc1/dashboard`) | Tous les rôles autorisés dans le bloc ; les Managers ne voient que leurs demandes | Manager : ses demandes ; RH, Finance, Direction : consultation selon leur besoin ; Administrateur |
| Nouvelle demande (`/bloc1/demande-form`) | Manager, RH et Administrateur. Le menu l’affiche aussi à Finance et Direction, dont l’accès est ensuite refusé par le filtre | Manager ; RH si elle peut saisir une demande pour le compte d’un service ; Administrateur |
| Analyse RH (`/bloc1/rh-review`) | RH, Direction et Administrateur | RH et Administrateur pour instruire et décider ; Direction éventuellement en consultation |
| Avis Finance (`/bloc1/finance-review`) | Finance, Direction et Administrateur | Finance et Administrateur pour décider ; Direction éventuellement en consultation |
| Décisions Direction (`/bloc1/direction-review`) | Direction et Administrateur | Direction et Administrateur |
| Postes associés (`/bloc1/positions`) | Tous les rôles autorisés dans le bloc | RH et Administrateur ; Direction et Manager en consultation selon le besoin |
| Historique demandes (`/bloc1/history`) | RH, Finance, Direction et Administrateur | RH, Finance, Direction et Administrateur, avec visibilité adaptée au rôle |

## Bloc 2 — Recrutement

| Page | Rôles appliqués |
|---|---|
| Suivi du recrutement (`/bloc2/dashboard`) | RH et Administrateur |
| Offres d’emploi (`/bloc2/offers`) | RH et Administrateur |
| Créer/modifier une offre (`/bloc2/offers/new`, `/bloc2/offers/{id}`) | RH et Administrateur |
| Candidats (`/bloc2/candidates`) | RH et Administrateur |
| Candidatures (`/bloc2/applications`) | RH et Administrateur |
| Présélection (`/bloc2/preselection`) | RH et Administrateur |
| Entretiens (`/bloc2/interviews`) | RH et Administrateur pour gérer ; Manager pour lire les entretiens où il est participant |
| Évaluations (`/bloc2/evaluations`) | RH et Administrateur pour consulter et saisir ; Manager participant pour consulter et soumettre son propre avis |
| Décision finale (`/bloc2/decisions`) | RH et Administrateur |
| Historique recrutement (`/bloc2/recruitment-history`) | RH et Administrateur |

Le Manager est filtré par son identifiant salarié pour les entretiens et évaluations. Lorsqu’il soumet une évaluation, le serveur utilise son identifiant de session comme évaluateur et refuse l’accès aux entretiens auxquels il ne participe pas. Les actions de gestion des entretiens lui sont refusées.

## Bloc 3 — Sélection et embauche

| Page | Rôles appliqués |
|---|---|
| Candidats retenus (`/bloc3/selected`) | RH et Administrateur |
| Proposition d’embauche (`/bloc3/proposal-form`) | RH et Administrateur |
| Réponses candidats (`/bloc3/responses`) | RH et Administrateur |
| Validation Direction (`/bloc3/hire-validations`) | Direction et Administrateur |
| Dossiers salariés (`/bloc3/employees`) | RH et Administrateur |

La Direction n’a accès qu’aux routes de validation d’embauche du Bloc 3. RH et Administrateur peuvent accéder aux autres pages et actions du bloc.

## Bloc 4 — Contrats et onboarding

| Page | Rôles appliqués |
|---|---|
| Liste des contrats (`/bloc4/contracts`) | RH et Administrateur |
| Nouveau/modifier un contrat (`/bloc4/contract-form`) | RH et Administrateur |
| Renouvellements (`/bloc4/renewals`) | RH et Administrateur |
| Affectations (`/bloc4/assignment-b4`) | RH et Administrateur |
| Documents (`/bloc4/documents`, `/bloc4/document-form`) | RH et Administrateur |
| Liste des onboardings (`/bloc4/onboardings`) | RH et Administrateur ; Manager en lecture seule pour son département |
| Détail des tâches (`/bloc4/onboarding-detail/{id}`) | RH pour modifier ; Manager en lecture seule pour son département |
| Paramètres des contrats (`/bloc4/contract-settings`) | RH et Administrateur en lecture seule (aucune route de modification n’existe actuellement) |

La base ne contient pas de lien explicite entre un Manager et les salariés qu’il encadre. En attendant ce lien, le périmètre « son équipe » est représenté par les salariés du même département. Un Manager sans département n’obtient aucun onboarding. Les actions d’enregistrement de tâches lui sont refusées côté serveur.

## Règles communes appliquées

- Les routes des Blocs 2, 3 et 4 exigent une session et passent par un filtre de rôle. Les accès interdits renvoient HTTP 403.
- Administrateur est autorisé sur toutes les routes de ces trois blocs.
- Les menus des Blocs 2, 3 et 4 n’affichent que les pages autorisées au rôle courant. Le menu du Bloc 1 n’a pas été modifié.
- Finance n’a pas d’accès aux Blocs 2, 3 et 4.
- La sélection des profils RH, Manager, Finance, Direction et Administrateur reste un mécanisme de démonstration. Le portail Salarié utilise son email et son mot de passe individuels.

## Bloc 7 — Congés et absences

| Page / fonctionnalité | Rôle prévu | Portée |
|---|---|---|
| Portail salarié : demandes et soldes | Salarié (cumulable avec Manager ou RH) | Uniquement ses demandes, justificatifs, notifications et soldes |
| File de validation manager | Manager | Demandes qui lui sont routées comme manager direct |
| File de validation RH | RH | Demandes routées aux RH en l'absence de manager et supervision selon habilitation |
| Gestion des comptes salariés (`/bloc7/comptes-salaries`) | RH et Administrateur | Activer ou réinitialiser le mot de passe du compte lié au dossier employé |
| Gestion du calendrier et supervision des soldes | RH ; Administrateur pour administration technique | Accès de gestion, avec journalisation des modifications |
| Suivi opérationnel des absences | RH et rôles Bloc 7 autorisés | Selon le périmètre de la partie D |

Le socle SQL ajoute le rôle Salarié et une relation `employe_role` afin qu'un même compte puisse cumuler des rôles. Le compte salarié est lié à `employe.id`, avec un mot de passe haché dans `employe.mot_de_passe`; la RH peut l'activer ou le réinitialiser depuis `/bloc7/comptes-salaries`. Les routes Bloc 7 sont filtrées côté serveur, avec contrôle du périmètre par salarié et manager. Les autres profils conservent leur connexion de démonstration.
