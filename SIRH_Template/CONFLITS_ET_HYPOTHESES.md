# NovaRH — conflits, arbitrages provisoires et limites

Source : les documents `Prompt_1.md` à `Prompt_5.md` fournis par l'équipe. Il s'agit d'une maquette interactive **sans backend**. Les hypothèses ci-dessous ne remplacent pas une décision métier.

| Point détecté | Documents concernés | Risque | Traitement retenu dans ce prototype | À valider |
|---|---|---|---|---|
| Statuts globaux `BROUILLON`, `ATTENTE_HIERARCHIE`, etc., absents du référentiel SQL actuel | Bloc 1 | Workflow impossible à enregistrer tel quel | Statuts de démonstration explicites dans le JavaScript | Ajouter les statuts et l'étape hiérarchique en base |
| `Demande` ne contient pas l'intitulé et les compétences du poste avant approbation | Bloc 1 | Demande insuffisamment décrite avant création du poste | Champs de brouillon `jobTitle`, `skills`, `description` uniquement en mémoire locale | Ajouter des colonnes à Demande ou une table de détail |
| Finance facultative, sans condition précise de déclenchement | Bloc 1 | Validation pouvant être contournée | Choix « Finance requise » consigné à l'étape RH | Définir les cas où Finance est obligatoire |
| RH enregistre la décision finale sur les candidatures ; autorité d'arbitrage non formalisée en cas de désaccord | Bloc 2 | Responsabilités ambiguës | RH enregistre la décision après lecture des avis ; ces avis sont distincts de la décision | Définir l'arbitre final du recrutement |
| Participation du Manager à un entretien sans table de liaison dans le SQL du Bloc 2 | Bloc 2 | Accès manager trop large | Liste `participants` dans chaque entretien de démonstration, sans table persistée | Créer `entretien_participant` avec les droits appropriés |
| Création du dossier salarié et affectation dans le Bloc 3 ; affectation à nouveau évoquée au Bloc 4 | Blocs 3 et 4 | Deux affectations actives et contradictoires | Une seule collection `assignments` partagée ; Bloc 3 crée l'affectation initiale, Bloc 4 suit ses changements et postes libres | Valider la responsabilité de l'affectation initiale et ses modifications |
| Nom de la table du salarié : `salarie` ou `employe` | Blocs 3 et 4 | Relations incompatibles entre modules | Objet JavaScript unique `employees`, libellé affiché « salarié » | Choisir définitivement un nom de table et l'utiliser partout |
| Bloc 4 commence après proposition acceptée, mais le Bloc 3 exige encore validation Direction et création du dossier | Blocs 3 et 4 | Déclenchement trop tôt des contrats | Le Bloc 4 attend la transmission après acceptation + approbation Direction + création de salarié + affectation | Confirmer le point de déclenchement contractuel |
| Règles de durée des CE/CDD/CDI et renouvellements | Cahier initial et Bloc 4 | Risque d'inexactitude juridique ou métier | Paramètres éditables (CE 6 mois, CDD 24 mois, CDI sans fin), maximum un renouvellement dans la démo ; aucune valeur n'est présentée comme conseil juridique | Confirmer les règles d'entreprise et droit applicable |
| Poste lié à une seule demande alors que le besoin peut porter sur plusieurs personnes | Cahier initial et Bloc 1 | Mauvaise gestion des postes ouverts multiples | Un poste dédié par demande, suivant l'unicité indiquée dans le Bloc 1 ; offre distincte ; une affectation ne peut occuper un poste déjà pris | Clarifier si le poste désigne une fiche métier, un poste budgété ou une place individuelle |
| Offre publiée sur un site externe vs. état de publication interne | Bloc 2 | Fausse impression d'une publication publique | Le bouton « Publier » ne change que le statut local, sans site extérieur | Définir éventuelle diffusion multi-canaux |
| Offre clôturée et candidats déjà en cours d'entretien | Bloc 2 | Arrêt injustifié de procédures lancées | L'ajout d'une nouvelle candidature est interdit ; les dossiers déjà saisis restent traitables | Confirmer les possibilités après clôture |
| Rôle Administrateur et décisions métiers | Tous les blocs | Privilèges excessifs | L'administrateur gère utilisateurs, départements, contrats paramétrés, postes ; il ne valide pas les décisions RH/Direction | Définir délégations exceptionnelles |
| Onboarding du manager et périmètre des droits | Bloc 4 | Accès aux informations de tous les salariés | Le manager voit les onboardings des salariés dont il est responsable dans l'affectation de démo | Confirmer l'étendue exacte de l'accès |

## Points volontairement hors champ

- Aucun portail de candidat ni de salarié en V1.
- Aucun envoi d'email, connexion PostgreSQL, serveur CodeIgniter, API, signature électronique ni authentification réelle.
- Le stockage local du navigateur sert à tester l'interface, **pas** à gérer des données RH de production.
- Les fichiers existants dans les données de départ sont des **métadonnées fictives**, pas de vrais PDF. Seuls les petits fichiers ajoutés manuellement peuvent être récupérés dans le même navigateur.
- Le champ de disponibilité multiple d'un poste, des critères d'autorité de recrutement, une éventuelle table de tests techniques et les conditions exactes de Finance restent à définir en équipe.
