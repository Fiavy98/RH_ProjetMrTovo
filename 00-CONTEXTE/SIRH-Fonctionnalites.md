# Fonctionnalités de l’application SIRH

## Portée de l’inventaire

Cet inventaire décrit les fonctionnalités repérées dans le code applicatif, les routes, les vues, les services et les documents du dépôt. Les répertoires `vendor`, `writable`, `tests` et `system` n’ont pas été analysés.

Les indications suivantes permettent de distinguer le niveau réel d’avancement :

- **Implémenté côté application** : une route et un traitement applicatif existent ; cela ne signifie pas que chaque parcours a été testé ici.
- **Partiel / générique** : l’écran existe, mais il s’agit surtout d’un squelette, d’une démonstration ou d’un traitement incomplet.
- **Prototype statique** : interaction simulée dans `SIRH_Template`; elle ne constitue pas une fonctionnalité serveur de l’application CodeIgniter.
- **Cadrage** : fonctionnalité décrite dans les documents mais non confirmée comme opérationnelle dans l’application.

## 1. Socle de l’application

### Interface commune

- Application web construite avec CodeIgniter 4, vues PHP, feuilles CSS et JavaScript.
- Mise en page commune avec navigation entre les blocs et affichage des pages des différents modules.
- Page d’accueil avec compteurs des départements, salariés, demandes, postes, candidats, contrats et onboardings.
- Affichage d’un message lorsque la base de données n’est pas accessible.
- Routes organisées par bloc dans des fichiers séparés (`bloc1.php` à `bloc4.php`).
- Menus dédiés aux Blocs 1 à 4.

### Accès et profils

- Écran de sélection d’un employé actif disposant d’un rôle enregistré.
- Ouverture d’une session applicative et déconnexion.
- Filtre d’accès qui redirige vers la sélection d’utilisateur les personnes sans session.
- Le rôle choisi est conservé en session et utilisé pour afficher le profil courant.
- Le Bloc 1 applique un filtre supplémentaire par rôle (`manager`, `rh`, `finance`, `direction`, `admin`) et limite aussi la liste des demandes d’un manager à ses propres demandes.
- Dans les routes des Blocs 2, 3 et 4, le contrôle observé est l’authentification par session ; aucun filtre de rôle équivalent au Bloc 1 n’est déclaré sur ces groupes.
- La sélection d’un employé ne demande pas de mot de passe : il s’agit d’un mécanisme de démonstration/sélection de profil, pas d’une authentification complète.

### Outils de développement

- Page `/dev/db`, disponible hors production, pour vérifier la connexion et compter les enregistrements des principales tables.
- La page de contrôle affiche également quelques employés via les modèles/entités.

## 2. Bloc 1 — Expression et validation des besoins

### Fonctionnalités reliées à la base

- Création d’une demande de recrutement avec demandeur courant, département, type de contrat, intitulé, nombre de postes, motif et budgets min/max facultatifs.
- Validation des champs obligatoires, de la longueur de l’intitulé, du nombre de postes positif et de la cohérence des budgets.
- Attribution du statut initial `RH_attente`.
- Vue d’ensemble des demandes, liste des postes associés et écrans de revue.
- File d’analyse RH des demandes en attente.
- Décision RH : validation avec budget prévisionnel et commentaire, ou refus avec commentaire.
- Transmission d’une demande validée par RH à la file Finance.
- Avis Finance : valider, refuser ou demander un complément, avec commentaire obligatoire.
- Transmission d’un avis positif Finance à la Direction.
- File de décision Direction, consultation du détail et ajout d’un commentaire.
- Décision Direction : approbation ou refus.
- Création de postes rattachés à une demande approuvée par la Direction ; une demande peut produire plusieurs postes.
- Consultation de l’historique d’une demande et de ses validations/commentaires.
- Liste de l’historique des demandes.
- Menus et accès Bloc 1 modulés selon le rôle.

### Limites observées

- Le formulaire de demande utilise l’employé connecté comme demandeur.
- Le filtre d’accès du Bloc 1 contrôle les segments de route selon le rôle, mais les comportements doivent être vérifiés avec les routes et autorisations réellement attendues pour chaque profil.
- Le squelette partagé contient aussi des pages génériques non spécifiques au workflow métier.

## 3. Bloc 2 — Recrutement

Le Bloc 2 dispose d’un contrôleur et d’un service métier dédiés (`RecruitmentService`) qui lisent et modifient les données de recrutement.

### Tableau de suivi

- Compteurs de suivi du recrutement.
- Liste des demandes, offres, candidatures et entretiens utilisée par les vues du bloc.

### Offres d’emploi

- Liste et filtrage des offres.
- Création et modification d’une offre.
- Association d’une offre à une demande approuvée et à un poste précis issu de cette demande.
- Affichage des postes disponibles.
- Publication et clôture d’une offre.
- Vérification de disponibilité du poste : une offre en brouillon/publiée ou une affectation active peut empêcher une autre offre de viser le même poste.
- Les nouvelles offres et leur publication nécessitent une demande dont l’état courant et l’historique de validation confirment l’approbation de la Direction.

### Candidats et candidatures

- Répertoire des candidats, recherche textuelle, consultation du détail et modification/création de fiche.
- Consultation des candidatures et création d’une candidature associant un candidat à une offre ouverte.
- Suivi de l’état de candidature.
- Présélection d’une candidature ou rejet motivé.
- Prévention de certaines transitions de statut non autorisées.

### Entretiens et évaluations

- Planification et modification d’un entretien pour une candidature présélectionnée ou déjà dans le processus d’entretien.
- Types d’entretien prévus par le code : RH, technique, final et test technique.
- Association d’un ou plusieurs employés actifs comme participants/évaluateurs.
- Une seule séance peut rester planifiée à la fois pour une candidature ; une candidature en entretien peut recevoir des séances successives.
- Marquage d’un entretien comme réalisé ou annulé ; le compte rendu est requis pour le marquer réalisé.
- Consultation des entretiens réalisés.
- Évaluation d’un entretien réalisé par un participant affecté, avec note de 0 à 20, avis et commentaire.
- Prévention d’une seconde évaluation par le même évaluateur pour la même séance.

### Décision finale et historique

- Décision finale de candidature retenue ou rejetée.
- Exigence d’au moins une évaluation et absence d’entretien encore planifié avant décision.
- Motif requis pour un rejet.
- Réservation d’un poste pour une seule candidature retenue ; clôture de l’offre publiée lors de la sélection.
- Historique des actions de recrutement avec filtres par action, entité, utilisateur et période.
- Journalisation transactionnelle des événements de recrutement dans `historique_recrutement`.

### Limites observées

- Le Bloc 2 ne crée pas le salarié et ne réalise pas l’affectation : ces étapes relèvent du Bloc 3.
- Les routes du Bloc 2 requièrent une session, mais le groupe de routes ne déclare pas de filtre serveur qui réserve les mutations à RH. Le cadrage de rôle et la sécurité effective ne sont donc pas équivalents.
- Les événements utilisent actuellement un auteur nul dans le journal tant que l’identité authentifiée n’est pas intégrée au modèle utilisateur.

## 4. Bloc 3 — Sélection et embauche

Le Bloc 3 est mixte : certaines pages interrogent directement PostgreSQL et enregistrent des changements ; d’autres entrées du menu passent par les pages génériques de démonstration du socle.

### Actions présentes dans les contrôleurs dédiés

- Consultation des candidatures retenues par le Bloc 2.
- Préparation/enregistrement d’une proposition d’embauche pour une candidature retenue : salaire, type de contrat, date de début et date de proposition.
- Vérification du salaire proposé par rapport à la fourchette budgétaire de la demande, lorsqu’elle est renseignée.
- Réponse du candidat à une proposition : acceptée ou refusée, avec date de réponse.
- File des propositions acceptées pour la validation de la Direction.
- Enregistrement d’une décision Direction validée ou refusée, date et commentaire.
- Consultation des dossiers salariés et des informations reliées au candidat, à l’offre, au département et à l’affectation.
- Formulaire permettant la création d’un dossier salarié rattaché à un candidat/département avec date d’embauche et statut actif (selon le contrôleur dédié).

### Écrans encore génériques ou incomplets

- Le tableau de bord de suivi des embauches est rendu par le contrôleur générique et affiche les lignes d’exemple/session plutôt qu’un workflow Bloc 3 complet.
- Les pages `assignment-b3` et `handoff` du menu sont déclarées dans le service de pages génériques ; aucune route dédiée de création d’affectation Bloc 3 ou de transmission vers le Bloc 4 n’a été repérée dans les routes dédiées.
- Le parcours de validation de la Direction et les actions de proposition/réponse/dossier sont réalisés par des contrôleurs spécifiques, mais plusieurs mises à jour directes de modèles ne présentent pas le même niveau de validation métier, transaction et journalisation que le service du Bloc 2.
- Il faut donc considérer le processus complet retenu → proposition → réponse → validation → salarié → affectation → transmission comme partiellement intégré, et non comme un flux automatisé de bout en bout.

## 5. Bloc 4 — Contrats, affectations de simulation et onboarding

Le Bloc 4 comporte des routes, vues, contrôleurs et services dédiés, avec opérations en base.

### Contrats

- Liste et filtrage des contrats par type, statut ou informations d’employé.
- Création d’un contrat brouillon et modification d’un brouillon existant.
- Consultation du détail : salarié, type, dates, salaire, statut, contrat précédent/suivant et documents liés.
- Types gérés : CE, CDD et CDI.
- Contrôle des champs, du salaire, de la cohérence début/fin et des chevauchements de périodes contractuelles pour un salarié.
- CDI sans date de fin ; CE et CDD avec date de fin obligatoire.
- Contrôle des durées maximales en mois calendaires selon les paramètres en base.
- Transitions disponibles : brouillon vers signé ; signé vers en cours ; en cours vers terminé pour les contrats à date de fin ; annulation d’un brouillon ou d’un contrat signé.
- Date de signature posée lors de l’action de signature.
- Écran de paramètres qui consulte les durées maximales et le nombre de renouvellements enregistrés pour les types de contrat.

### Renouvellements

- Liste des contrats potentiellement renouvelables.
- Création d’un nouveau contrat lié au précédent.
- Réutilisation du salarié et du type de contrat précédent.
- Début du renouvellement fixé au lendemain de la date de fin précédente.
- Refus d’un renouvellement si le contrat précédent n’est pas terminé, s’il a déjà un enfant/renouvellement, s’il est lui-même un renouvellement, ou s’il n’a pas de date de fin.
- Validation de la durée du nouveau contrat selon le type.

### Onboarding

- Création d’un dossier d’onboarding lors de la première signature d’un contrat initial, s’il n’existe pas déjà pour le salarié.
- Initialisation de six tâches à `a_faire` à partir des six types de tâche présents en base ; l’opération de signature échoue si cette configuration n’est pas complète.
- Liste et détail de l’onboarding d’un salarié.
- Mise à jour groupée des statuts/données des tâches sur un écran.
- Recalcul du statut global de l’onboarding à partir des tâches et gestion de la date d’achèvement selon leur état.

### Documents

- Liste des documents et formulaire de dépôt.
- Association des métadonnées à l’employé et au contrat.
- Dépôt serveur dans l’espace d’écriture privé de l’application.
- Contrôle de fichier selon les types/MIME admis et vérification de la cohérence du contrat et du salarié.
- Téléchargement par une route applicative qui vérifie le chemin stocké et l’existence du fichier.

### Affectation provisoire

- Vue des employés, postes vacants et affectations existantes.
- Création d’une affectation de simulation dans la table partagée `affectation`.
- Refus d’un employé déjà affecté activement ou d’un poste déjà occupé/non vacant.
- Mise à jour du statut du poste vers `POURVU` au sein de l’opération.
- Cette implémentation est temporaire en attendant l’intégration de la fonctionnalité d’affectation Bloc 3.

### Limites observées

- Le groupe de routes du Bloc 4 protège par session, sans autorisation par rôle.
- L’onboarding est créé à la signature du contrat initial ; le renouvellement ne doit pas créer un onboarding supplémentaire.
- Les règles de fin de contrat et les états doivent être lus dans le service et le cadrage : ne pas assimiler les écrans génériques à des opérations métier.

## 6. Pages génériques de démonstration

Le service commun `BlocPageService` produit des pages à partir de listes d’écrans et de données d’exemple. Les formulaires génériques vérifient principalement les champs obligatoires puis enregistrent les saisies en session, pas dans les tables métier.

Selon le parcours, les entrées génériques de page peuvent servir de squelette pour :

- tableaux de bord et listes ;
- formulaires de demande/offre/candidat/candidature ;
- formulaires d’évaluation, de proposition, de décision, d’affectation ou de document ;
- certains écrans de suivi ou de transmission.

Lorsqu’une route dédiée existe (par exemple les offres du Bloc 2 ou contrats du Bloc 4), elle prime sur la page générique. Une page générique ne doit pas être considérée comme la preuve que le workflow correspondant est implémenté en base.

## 7. Prototype statique `SIRH_Template`

Le dossier contient une maquette autonome HTML/CSS/JavaScript, distincte du backend CodeIgniter. Son README décrit des interactions de démonstration et un stockage local navigateur, notamment :

- navigation, sidebar, profils fictifs, recherche, filtres, tri, pagination, formulaires et confirmations ;
- simulation des workflows des Blocs 1 à 4 ;
- administration fictive, matrice de rôles, départements et paramètres.

Ces éléments servent de référence visuelle/fonctionnelle. Ils ne signifient pas que les mêmes fonctions sont toutes branchées au serveur ou sécurisées dans l’application PHP.

## 8. Fonctions décrites dans le cadrage mais non confirmées comme opérationnelles

À partir des menus, pages génériques et documents du projet, les sujets suivants restent à intégrer ou à confirmer au niveau de l’application :

- authentification réelle avec identifiants/mot de passe et gestion de compte ;
- autorisations par rôle appliquées côté serveur à toutes les routes et mutations ;
- affectation initiale Bloc 3 unifiée avec l’affectation temporaire Bloc 4 ;
- transmission explicite d’un dossier salarié du Bloc 3 au Bloc 4 ;
- automatisation complète de la chaîne Bloc 2 → Bloc 3 → Bloc 4 ;
- administration réelle des utilisateurs, rôles, départements et paramètres ;
- journaux d’audit complets pour toutes les opérations et identité de l’auteur ;
- activation et vérification de la protection CSRF globale ;
- correspondance complète entre les règles de chaque document de cadrage et les validations effectivement appliquées par les contrôleurs.

## 9. Modèle de données couvert par les fonctionnalités

Les modèles et scripts SQL du dépôt montrent notamment les entités suivantes :

- référentiels : `departement`, `role`, `status`, `type_contrat`, `type_tache_onboarding` ;
- besoins et postes : `demande`, `validation`, `poste` ;
- recrutement : `offre_emploi`, `candidat`, `candidature`, `entretien`, participants d’entretien et `evaluation_candidat` ;
- embauche et suivi : `proposition_embauche`, `employe`, `affectation`, `historique_recrutement` ;
- contrat et intégration : `contrat`, `document`, `onboarding`, `onboarding_tache`.

Ce modèle décrit les objets persistés ; la présence d’un modèle ou d’une table seule ne garantit pas qu’une interface permet déjà de réaliser toutes les opérations sur cet objet.

## 10. Statut de validation de cet inventaire

Il s’agit d’une analyse statique du dépôt et de ses documents. Aucune fonctionnalité n’a été exécutée ni testée dans le cadre de la création de ce fichier. Les formulations « implémenté » indiquent la présence de code/route/traitement repérés, pas une recette fonctionnelle réussie.
