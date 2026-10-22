# NovaRH — template frontend complet du SIRH (V1)

Prototype autonome construit à partir des cinq documents de l'équipe : exigences générales et Blocs 1 à 4. **Ce n'est pas encore une application CodeIgniter** et il n'y a **aucun backend**.

## Livrables

```text
SIRH_Template/
├── index.html                  # Un seul fichier HTML, point d'entrée de toutes les vues
├── styles.css                  # Charte graphique, tableaux, composants, responsive
├── app.js                      # Navigation, jeu de données, écrans et interactions simulées
├── CONFLITS_ET_HYPOTHESES.md  # Décisions provisoires à valider en équipe
└── README.md                  # Ce guide
```

## Démarrage

1. Décompresser l'archive ZIP.
2. Ouvrir `index.html` dans Chrome, Edge ou Firefox.
3. Choisir un profil : **Manager**, **Responsable hiérarchique**, **RH**, **Finance**, **Direction** ou **Administrateur**.
4. Pour le formulaire de connexion fictif, saisir `demo` / `demo`.
5. Naviguer dans la sidebar. Le sélecteur de rôle en haut permet de changer de profil de démonstration sans se déconnecter.

Si votre navigateur empêche l'accès à `localStorage` depuis un fichier local, lancer un serveur de fichiers statiques dans ce dossier :

```bash
python -m http.server 8000
```

Puis ouvrir `http://localhost:8000`.

Aucune bibliothèque ou base de données externe n'est nécessaire. Les polices personnalisées ne sont pas obligatoires ; la feuille CSS utilise des polices de secours du système.

## Ce que l'on peut tester

### Écrans de base et de navigation

- Connexion fictive avec affichage/masquage du mot de passe et validation.
- Sidebar repliable, sous-menus, fil d'Ariane, en-tête, profils métier et déconnexion.
- Tableau de bord dynamique et accès rapides.
- Recherche, filtres, tri, pagination, fiches, formulaires, messages d'erreur et confirmations.
- Interface responsive (desktop, tablette, téléphone).

### Bloc 1 : expression du besoin

- Création de demande en brouillon, correction et examen avant soumission.
- Responsable hiérarchique : approbation, refus, retour.
- RH : contrôles du besoin, édition du budget prévisionnel et avis.
- Finance : avis Oui / Non, facultatif selon le circuit ; sans modification du budget.
- Direction : décision finale.
- Création d'un poste rattaché à une demande approuvée, historique.

### Bloc 2 : recrutement

- Offres en brouillon, publication et clôture simulées.
- Fiches candidats indépendantes des candidatures ; interdiction du doublon candidat/offre.
- Présélection, rejet motivé, entretien / test, planification et modification.
- Manager limité aux entretiens auxquels il est affecté.
- Évaluations avec notes, avis et commentaires distincts de la décision finale.
- RH enregistre la candidature retenue ou rejetée ; historisation.
- Une candidature retenue **ne devient pas automatiquement salariée**.

### Bloc 3 : sélection et embauche

- Candidats retenus, préparation de propositions, envoi fictif.
- Saisie RH de la réponse du candidat (acceptation / refus).
- Validation de la Direction après acceptation.
- Création d'un salarié seulement après ces deux conditions ; matricule unique.
- Affectation initiale à un poste libre et transmission au Bloc 4.

### Bloc 4 : contrats et onboarding

- Contrats CE, CDD, CDI avec validations de dates.
- Cycle : brouillon → signé → en cours → terminé, avec possibilité d'annuler aux étapes prévues.
- Renouvellement distinct, lié au contrat d'origine, type et salarié conservés, contrôles de durée et d'unicité.
- Gestion des postes libres et des changements d'affectation dans une **même collection** que celle du Bloc 3.
- Documents : simulation de choix et de stockage local des petits fichiers (700 Ko maximum) ; l'ajout n'est **pas** un upload serveur.
- Suivi de l'onboarding : **exactement six tâches fixes**, statut global calculé, responsables, échéances et dates de réalisation.
- Paramètres contractuels modifiables par l'administrateur.

### Administration

- Ajouter / modifier des utilisateurs fictifs, voir la matrice de rôles, gérer les départements et paramètres de contrats.

## Un scénario de démonstration guidé

1. Ouvrir en **RH** `Recrutement → Offres d'emploi`. O-1 (Développeur Java) est publiée et vient de D-1, approuvée au Bloc 1.
2. Aller à `Candidatures`. Une nouvelle candidature ne peut être créée que pour une offre publiée et un candidat existant.
3. Dans `Présélection`, présélectionner une candidature en attente et planifier un entretien.
4. Terminer un entretien en saisissant un compte rendu. En tant que **Manager**, évaluer l'entretien affecté au profil ; revenir en **RH** pour enregistrer la décision finale.
5. Dans `Sélection et embauche`, préparer la proposition, simuler son envoi, saisir l'acceptation. En tant que **Direction**, approuver l'embauche.
6. Revenir en **RH** : créer le salarié, réaliser son affectation initiale sur **un poste effectivement libre**, puis transmettre au Bloc 4.
7. Créer un contrat, le signer, déposer localement un document si nécessaire et suivre les six tâches d'onboarding.

Le jeu de démonstration contient également un parcours déjà avancé pour Jean Andry : candidature retenue, proposition validée et transmise, dossier salarié, contrat signé et onboarding partiellement terminé. Il est utile pour tester immédiatement le Bloc 4.

**Important :** le poste « Développeur Java » de cette démo est déjà affecté. Pour tester une nouvelle embauche complète sans contourner les règles, créez et faites approuver une nouvelle demande ou partez du poste de Comptable s'il est libre. Plusieurs recrutements pour un même poste nécessitent une conception métier complémentaire.

## Stockage et limites

- Les modifications sont enregistrées par `localStorage` dans **ce navigateur uniquement** et ne sont ni synchronisées ni persistées sur serveur. La sélection du rôle de démonstration utilise `sessionStorage`.
- Bouton « Réinitialiser la démo » sur le tableau de bord pour restaurer le jeu initial.
- La visibilité des actions selon les rôles n'est **pas** une autorisation sécurisée ; elle devra être refaite côté serveur.
- Ne jamais saisir de véritables données personnelles RH dans ce prototype sans cadre de sécurité.
- Les valeurs de durée de contrat sont des règles pédagogiques **à confirmer**, non une déclaration de conformité au droit du travail.
- Les rôles, statuts et relations non stabilisés sont documentés dans `CONFLITS_ET_HYPOTHESES.md`.

## Reprise future sous CodeIgniter

Conserver les IDs et les relations fonctionnelles, puis déplacer progressivement les opérations de `app.js` vers les contrôleurs, modèles et services de CodeIgniter :

- Connexion et sessions sécurisées ; RBAC côté serveur ; journaux d'audit.
- PostgreSQL et migrations de tables des quatre blocs.
- API / endpoints pour CRUD, validations de workflow, états et transactions.
- Association de `entretien_participant` aux utilisateurs ; validation serveur des règles.
- Pièces jointes sur un stockage sécurisé ; métadonnées en base, contrôle des accès.
- Contrôle des données personnelles, confidentialité et traçabilité.

L'HTML, les classes CSS et les fonctions de rendu frontend peuvent être migrés dans des vues CodeIgniter, sans introduire Spring Boot.

## Contrôles effectués

- Syntaxe JavaScript : `node --check app.js` : **OK**.
- Rendu des écrans par profils dans un environnement JavaScript isolé : **55 combinaisons écran/profil contrôlées sans exception**.
- Concordance des actions visibles et des gestionnaires JavaScript : **OK**.
- Test graphique complet dans Chromium : **non effectué** ; le navigateur automatisé de cet environnement bloque les fichiers et `localhost` avec `ERR_BLOCKED_BY_ADMINISTRATOR`. Une validation manuelle en navigateur reste requise pour livrer en production.
