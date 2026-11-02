# Rôles et accès aux pages du SIRH

## Portée

Ce document récapitule les pages accessibles depuis les menus de l’application, les rôles actuellement contrôlés côté serveur et les rôles recommandés.

> **Important :** le Bloc 1 possède un filtre d’accès par rôle. Les routes des Blocs 2, 3 et 4 vérifient seulement qu’un utilisateur a une session ouverte : les rôles recommandés dans les tableaux ci-dessous doivent donc être appliqués côté serveur. Masquer un lien dans le menu ne suffit pas à protéger une page ou une action.

Rôles considérés : **Manager**, **RH**, **Finance**, **Direction**, **Administrateur**. Dans le cadrage V1, le candidat et le salarié n’accèdent pas à l’application ; leurs données sont saisies ou gérées par la RH.

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

Le groupe de routes actuel exige seulement une session. Les rôles recommandés ci-dessous doivent être **appliqués côté serveur**.

| Page | Rôles recommandés |
|---|---|
| Suivi du recrutement (`/bloc2/dashboard`) | RH et Administrateur ; éventuellement Direction pour une vue de synthèse |
| Offres d’emploi (`/bloc2/offers`) | RH et Administrateur |
| Créer/modifier une offre (`/bloc2/offers/new`, `/bloc2/offers/{id}`) | RH et Administrateur |
| Candidats (`/bloc2/candidates`) | RH et Administrateur |
| Candidatures (`/bloc2/applications`) | RH et Administrateur |
| Présélection (`/bloc2/preselection`) | RH et Administrateur |
| Entretiens (`/bloc2/interviews`) | RH pour planifier et gérer ; Manager uniquement pour consulter les entretiens auxquels il participe |
| Évaluations (`/bloc2/evaluations`) | Manager participant à l’entretien ; RH et Administrateur selon le processus d’évaluation |
| Décision finale (`/bloc2/decisions`) | RH et Administrateur |
| Historique recrutement (`/bloc2/recruitment-history`) | RH et Administrateur ; Direction en consultation si nécessaire |

## Bloc 3 — Sélection et embauche

Le groupe de routes actuel exige seulement une session. Les rôles recommandés ci-dessous doivent être **appliqués côté serveur**.

| Page | Rôles recommandés |
|---|---|
| Candidats retenus (`/bloc3/selected`) | RH et Administrateur |
| Proposition d’embauche (`/bloc3/proposal-form`) | RH et Administrateur |
| Réponses candidats (`/bloc3/responses`) | RH et Administrateur — la réponse du candidat est enregistrée par la RH |
| Validation Direction (`/bloc3/hire-validations`) | Direction et Administrateur |
| Dossiers salariés (`/bloc3/employees`) | RH et Administrateur ; Direction en consultation si nécessaire |

## Bloc 4 — Contrats

Le groupe de routes actuel exige seulement une session. Les rôles recommandés ci-dessous doivent être **appliqués côté serveur**.

| Page | Rôles recommandés |
|---|---|
| Liste des contrats (`/bloc4/contracts`) | RH et Administrateur |
| Nouveau/modifier un contrat (`/bloc4/contract-form`) | RH et Administrateur |
| Renouvellements (`/bloc4/renewals`) | RH et Administrateur |
| Affectations (`/bloc4/assignment-b4`) | RH et Administrateur |
| Documents (`/bloc4/documents`, `/bloc4/document-form`) | RH et Administrateur |
| Liste des onboardings (`/bloc4/onboardings`) | RH et Administrateur ; Manager en consultation pour son équipe |
| Détail et suivi des tâches (`/bloc4/onboarding-detail/{id}`) | RH pour modifier ; Manager en consultation ou participation limitée à son équipe |
| Paramètres des contrats (`/bloc4/contract-settings`) | Administrateur pour modifier ; RH éventuellement en consultation |

## Mise en œuvre de l’accès

Les menus des Blocs 2, 3 et 4 ne filtrent actuellement pas les entrées par rôle. Il faut ajouter les autorisations serveur aux routes de lecture **et** aux actions POST, puis aligner l’affichage des menus sur les mêmes règles. Pour les pages qui incluent des données sensibles, les filtres doivent aussi limiter les enregistrements visibles (par exemple, un Manager ne voit que ses demandes ou les entretiens auxquels il participe).
