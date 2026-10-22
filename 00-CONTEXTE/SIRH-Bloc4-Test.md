# Plan de test manuel — Bloc 4 : Contrat et onboarding

> Ce document décrit les vérifications à effectuer. Les tests ci-dessous ne sont pas déclarés exécutés.

## 1. Périmètre

Vérifier les parcours Bloc 4 disponibles :

- consultation, création et modification d’un contrat brouillon ;
- règles de durée et de renouvellement des CE, CDD et CDI ;
- transitions d’état du contrat et prise en compte des dates ;
- création de l’onboarding après signature, suivi des six tâches et enregistrement groupé ;
- dépôt et téléchargement de documents ;
- affectation de simulation en attendant l’intégration du Bloc 3 ;
- consultation des paramètres de types de contrat.

La gestion des rôles et permissions est hors périmètre pour cette version, conformément aux décisions du projet.

## 2. Préparation de l’environnement

1. Utiliser une base de développement isolée ou jetable. Ne pas exécuter les scripts de données de démonstration sur une base contenant des données à conserver.
2. Créer la structure avec `database/sql/db_SIRH_fusion.sql`, puis charger les données de démonstration avec `database/sql/db_SIRH_donnees_test.sql`.
3. Vérifier la configuration PostgreSQL de l’application dans `.env`, puis démarrer l’application selon les instructions du dépôt.
4. Ouvrir l’URL locale/virtual host configurée pour ce projet.
5. Vérifier que les types de contrat CE, CDD et CDI ainsi que les six types de tâche d’onboarding sont présents.
6. Préparer un employé fictif dédié, rattaché aux données de référence requises, sans contrat ni affectation active, pour les scénarios de création et de renouvellement. Les données de démonstration contiennent déjà des contrats actifs ou brouillons pour la plupart des employés.
7. Pour les essais de documents, n’utiliser que des fichiers fictifs sans données personnelles réelles.

### Données de démonstration utiles

| Donnée | Usage conseillé | Précaution |
|---|---|---|
| Employé 9, Toky | Consulter un contrat CE brouillon | Il possède déjà un contrat brouillon. Ne pas l’utiliser pour un autre contrat qui ferait chevaucher les dates. |
| Employé 10, Lalaina | Parcours d’un contrat CE signé et onboarding | Son contrat est daté dans le futur par rapport aux données fournies. Les transitions dépendent de la date système. |
| Employé 8, Nirina | Onboarding avec tâches à différents états | Onboarding déjà partiellement traité. |
| Employé 7, Miora | Consulter un onboarding terminé et l’historique de contrats | Son historique contient déjà un renouvellement CE ; ne pas s’en servir pour tester un second renouvellement réussi. |
| Employés 1 à 6 | Candidats possibles aux tests d’affectation, après vérification en base | Vérifier qu’ils n’ont pas d’affectation active avant l’essai. |
| Postes 6 et 7 | Candidats possibles à l’affectation de simulation | Vérifier qu’ils sont toujours vacants avant l’essai. |

Les fichiers physiques associés aux documents de démonstration peuvent ne pas être présents. Pour valider le téléchargement, déposer un nouveau fichier fictif pendant le test.

## 3. Règles de durée à appliquer

La date de fin est le dernier jour couvert par le contrat. La durée maximale est calculée en mois calendaires à partir de la date de début ; le jour suivant la date de fin doit être au plus égal à la date de début augmentée de la durée autorisée, avec ajustement au dernier jour du mois lorsque nécessaire.

Exemples aux limites :

- CE de 6 mois débutant le 31 janvier 2027 : une fin au 30 juillet 2027 est dans la limite ; une fin au 31 juillet dépasse la limite.
- CDD de 12 mois débutant le 31 janvier 2027 : une fin au 30 janvier 2028 est dans la limite ; une fin au 31 janvier dépasse la limite.
- Un CE ou un CDD ne peut être renouvelé qu’une seule fois, même si la durée cumulée reste sous la durée maximale indiquée dans la décision.
- Un CDI n’a pas de date de fin et ne peut pas être renouvelé.

## 4. Cas de test

Pour chaque cas, noter le résultat observé et tout message d’erreur. Sauf indication contraire, commencer avec un employé fictif dédié et vérifier les données sauvegardées dans l’écran, puis en base si l’accès de test le permet.

### A. Contrats : consultation et saisie

| ID | Étapes | Résultat attendu |
|---|---|---|
| C-01 | Ouvrir la liste des contrats, parcourir les pages et utiliser les filtres proposés. | La liste s’affiche avec les informations prévues ; les filtres restituent les contrats correspondants ; les liens vers le détail fonctionnent. |
| C-02 | Ouvrir le formulaire de création, saisir les champs requis et enregistrer un CE brouillon valide pour l’employé de test. | Le contrat est créé en brouillon et apparaît dans la liste et son écran de détail. |
| C-03 | Saisir un CE débutant le 31/01/2027 et finissant le 30/07/2027. | L’enregistrement est accepté : la période ne dépasse pas six mois calendaires. |
| C-04 | Reprendre le cas C-03 avec une fin au 31/07/2027. | L’enregistrement est refusé avec une validation compréhensible de durée maximale. |
| C-05 | Saisir un CDD du 31/01/2027 au 30/01/2028. | L’enregistrement est accepté : la période ne dépasse pas douze mois calendaires. |
| C-06 | Reprendre le cas C-05 avec une fin au 31/01/2028. | L’enregistrement est refusé pour dépassement de la durée maximale du CDD. |
| C-07 | Saisir un CDI sans date de fin. | Le contrat est accepté sans date de fin. |
| C-08 | Saisir un CDI avec une date de fin. | La saisie est refusée ou la date de fin est empêchée selon l’interface ; aucun CDI avec fin ne doit être enregistré. |
| C-09 | Essayer une date de fin antérieure au début, une durée vide/invalide ou un salaire négatif. | Chaque donnée invalide est rejetée et le formulaire conserve les autres données saisies lorsque possible. |
| C-10 | Essayer d’enregistrer un contrat pour un employé ou un type inexistant/invalide, si le formulaire peut être soumis avec une valeur altérée. | La validation côté serveur refuse la référence invalide. |
| C-11 | Enregistrer un contrat dont la période chevauche un autre contrat non annulé du même employé. | L’enregistrement est refusé et l’employé n’obtient pas de contrats simultanés incompatibles. |
| C-12 | Vérifier le montant du salaire dans la création, le détail et la modification d’un brouillon. | Le montant est enregistré et restitué sans changement inattendu. |
| C-13 | Modifier un brouillon puis essayer de modifier un contrat signé/en cours. | Le brouillon est modifiable selon le formulaire ; les modifications interdites sur un contrat engagé sont refusées. |

### B. Contrats : états et dates

| ID | Étapes | Résultat attendu |
|---|---|---|
| C-14 | Signer un contrat brouillon valide. | Le statut devient signé, la date de signature est renseignée et un onboarding est créé pour le nouvel employé. |
| C-15 | Tenter de passer à « en cours » un contrat signé dont la date de début est future. | La transition est refusée jusqu’à la date de début. |
| C-16 | Pour un contrat signé dont la date de début est atteinte, déclencher la transition vers « en cours ». | Le statut passe à en cours. |
| C-17 | Annuler un brouillon, puis essayer une transition non autorisée depuis l’état annulé. | L’annulation est enregistrée ; une transition incompatible est refusée. |
| C-18 | Terminer un contrat à durée déterminée en cours dont la date de fin est atteinte. | Le contrat peut passer à terminé selon les règles du flux. Une date de fin future ne doit pas permettre une fin anticipée non prévue. |
| C-19 | Essayer de terminer un CDI en utilisant l’action de fin prévue pour les contrats à date de fin. | Le comportement suit la règle V1 documentée dans les écrans/flux ; aucun état incohérent ne doit être créé. |
| C-20 | Répéter une action de signature ou de transition déjà effectuée. | L’application ne crée ni doublon d’onboarding ni transition incohérente. |

### C. Renouvellement

Utiliser un employé fictif dédié et un contrat CE ou CDD antérieur terminé, sans renouvellement préalable.

| ID | Étapes | Résultat attendu |
|---|---|---|
| R-01 | Depuis le contrat terminé, lancer un renouvellement du même type, avec un début le lendemain de la date de fin précédente et une durée valide. | Le nouveau contrat est créé avec un lien vers le contrat précédent et la période n’a pas de chevauchement. |
| R-02 | Tenter un renouvellement dont le début n’est pas le lendemain de la fin précédente. | La demande est refusée selon la règle de continuité du projet. |
| R-03 | Pour un CE, choisir une période de renouvellement supérieure à six mois ; pour un CDD, supérieure à douze mois. | Le renouvellement est refusé pour dépassement de la limite par période. |
| R-04 | Après un renouvellement valide, demander un deuxième renouvellement du contrat initial ou du contrat de renouvellement. | Le deuxième renouvellement est refusé : un seul renouvellement est autorisé. |
| R-05 | Tenter de renouveler un CDI ou de changer le type de contrat lors du renouvellement. | L’opération est refusée. |
| R-06 | Tenter de renouveler un contrat qui n’est pas terminé. | L’opération est refusée. |

### D. Onboarding

| ID | Étapes | Résultat attendu |
|---|---|---|
| O-01 | Après la première signature d’un contrat pour l’employé dédié, ouvrir son onboarding. | Un onboarding unique existe avec exactement six tâches, toutes au statut `a_faire`. |
| O-02 | Ouvrir la liste puis le détail d’onboarding des employés 7, 8 et 10. | Les états existants sont affichés ; les six tâches d’un onboarding sont présentées sur un seul écran. |
| O-03 | Changer plusieurs statuts de tâches sur le même écran puis cliquer sur Enregistrer. | Toutes les modifications sont sauvegardées ensemble et réapparaissent après rechargement. |
| O-04 | Enregistrer un onboarding avec les six tâches à `a_faire`. | Le statut global est `a_faire`. |
| O-05 | Mettre au moins une tâche à `en_cours` et laisser les autres non terminées, puis enregistrer. | Le statut global devient `en_cours`. |
| O-06 | Mettre les six tâches à `termine`, puis enregistrer. | Le statut global devient `termine` et la date d’achèvement est renseignée. |
| O-07 | Après O-06, réouvrir une tâche et l’enregistrer dans un état non terminé. | Le statut global est recalculé ; la date d’achèvement n’indique plus une fin complète. |
| O-08 | Soumettre un statut de tâche inconnu ou une mise à jour incomplète/altérée, si possible depuis le formulaire. | La requête est refusée avec validation serveur et aucune mise à jour partielle n’est appliquée. |
| O-09 | Signer un autre contrat indépendant pour un employé qui possède déjà son onboarding. | Aucun deuxième onboarding ni jeu de tâches en double n’est créé. |

### E. Documents

| ID | Étapes | Résultat attendu |
|---|---|---|
| D-01 | Depuis un contrat, déposer un fichier PDF fictif autorisé. | Le fichier et ses métadonnées sont associés au bon contrat et visibles dans la liste des documents. |
| D-02 | Télécharger le fichier déposé en D-01. | Le téléchargement réussit et le contenu correspond au fichier envoyé. |
| D-03 | Répéter avec les autres formats autorisés affichés par l’application (par exemple JPEG, PNG, WebP ou texte). | Chaque format autorisé est accepté et récupérable. |
| D-04 | Essayer de déposer un fichier exécutable ou un format non autorisé. | Le dépôt est refusé ; aucun document invalide n’est associé au contrat. |
| D-05 | Essayer d’accéder au téléchargement avec un identifiant de document inexistant ou appartenant à un autre contrat. | L’accès est refusé ou le document est introuvable ; aucun fichier non associé n’est divulgué. |
| D-06 | Vérifier l’emplacement de stockage après un dépôt réussi. | Le fichier est conservé dans l’espace d’écriture prévu par l’application et n’est pas rendu public par une URL directe. |

### F. Affectation de simulation

| ID | Étapes | Résultat attendu |
|---|---|---|
| A-01 | Ouvrir le formulaire d’affectation et choisir un employé sans affectation active ainsi qu’un poste vacant (par exemple 6 ou 7, après vérification). | Les options disponibles sont affichées et le formulaire peut être soumis. |
| A-02 | Enregistrer l’affectation. | Une affectation de simulation est ajoutée en base et le poste passe à l’état pourvu. |
| A-03 | Essayer d’affecter une deuxième fois le même employé à un autre poste. | L’opération est refusée tant que sa première affectation est active. |
| A-04 | Essayer d’affecter un autre employé à un poste déjà pourvu. | L’opération est refusée. |
| A-05 | Vérifier la liste des postes et des affectations après A-02. | L’état du poste et l’affectation enregistrée sont cohérents. |

### G. Paramètres de contrat

| ID | Étapes | Résultat attendu |
|---|---|---|
| P-01 | Ouvrir l’écran des types de contrat. | Les paramètres indiquent CE : 6 mois maximum et 1 renouvellement ; CDD : 12 mois maximum et 1 renouvellement ; CDI : sans durée maximale définie et sans renouvellement. |
| P-02 | Comparer ces valeurs aux validations des cas C-03 à C-08 et R-03 à R-05. | Les valeurs affichées et appliquées par les validations concordent. |

## 5. Feuille de résultats

À remplir lors de l’exécution par l’équipe :

| ID du cas | Résultat (réussi/échoué/non exécuté) | Observations ou anomalie | Testeur | Date |
|---|---|---|---|---|
|  |  |  |  |  |
|  |  |  |  |  |
|  |  |  |  |  |

## 6. Remise à zéro après les tests

- Conserver les essais dans une base jetable et la réinitialiser selon la procédure de l’équipe après validation.
- Ne pas vider de tables ni supprimer des fichiers dans une base ou un espace partagé sans sauvegarde et vérification de l’environnement ciblé.
- Retirer uniquement les fichiers fictifs déposés par ces essais, en vérifiant leur nom et leur emplacement.
- Noter les anomalies avec l’identifiant du cas, les étapes exactes, les données utilisées et le résultat attendu/observé.
