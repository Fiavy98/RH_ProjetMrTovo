# Bloc 7 — Partie C : besoins métier

## 1. Périmètre

La partie C couvre le portail congés salarié, les demandes, leur décision, les droits acquis et leur solde, ainsi que les notifications liées au parcours. Elle s'appuie sur les contrats et les employés du socle. Les absences opérationnelles et paramètres détaillés restent raccordés à la partie D.

## 2. Règles métier décidées

| Sujet | Règle |
|---|---|
| Unité | Jour ouvré (lundi à vendredi, hors jours fériés configurés), utilisé pour acquisition et consommation. |
| Acquisition | 2,5 jours ouvrés à chaque mois complet de service, à chaque anniversaire mensuel de la date de début du contrat. Pas de crédit pour mois incomplet. |
| Disponibilité | Les droits sont utilisables dès leur acquisition. Une demande future est contrôlée uniquement sur les droits déjà acquis à la date de soumission. |
| Expiration | Chaque tranche mensuelle expire trois ans après sa date d'acquisition. Les tranches les plus anciennes sont consommées en premier (FIFO). Une demande refusée ne suspend ni ne reporte l'expiration. |
| Contrats | Le solde repart de zéro pour chaque nouveau contrat. Les droits et soldes du contrat précédent restent historiques et ne sont pas transférés. |
| Types | Congé annuel, maladie et exceptionnel. Seul le congé annuel consomme le solde annuel ; maladie et exceptionnel n'en déduisent pas. |
| Validation | Le salarié soumet sa demande à son manager direct ; s'il n'en a pas, elle est routée aux RH. Un seul de ces acteurs prend la décision. |
| Parcours | Une demande peut être approuvée, refusée, retournée pour correction ou retirée par le salarié selon son état. Les décisions et changements restent historisés. |
| Compte | Un compte applicatif est lié au dossier salarié. Le rôle Salarié s'ajoute aux rôles métier existants au besoin. |
| Notifications | Le salarié et le valideur reçoivent des notifications applicatives aux étapes importantes. |

Cette unité est une convention du projet. Le paramétrage du calendrier (jours fériés) doit être tenu à jour. La conformité de l'unité retenue avec les règles légales applicables devra être validée séparément.

## 3. Besoins fonctionnels

| ID | Besoin | Résultat attendu |
|---|---|---|
| B7C-01 | Consulter son solde | Le salarié voit ses droits acquis, consommés, réservés, disponibles et les dates d'expiration par contrat. |
| B7C-02 | Créer et soumettre une demande | Le salarié choisit le type et les dates ; le système calcule la durée en jours ouvrés et contrôle le solde acquis à la soumission pour un congé annuel. |
| B7C-03 | Routage | La demande est adressée au manager direct ou, en son absence, à la RH. |
| B7C-04 | Décider | Le valideur peut approuver, refuser ou demander une correction, avec commentaire et historique. |
| B7C-05 | Corriger et resoumettre | Le salarié peut corriger une demande retournée ; la nouvelle soumission relance la validation et les contrôles. |
| B7C-06 | Retirer | Le salarié peut retirer une demande selon son état ; une réservation éventuelle est libérée et l'historique est conservé. |
| B7C-07 | Acquérir les droits | Le système crédite 2,5 jours à chaque mois complet du contrat actif, sans anticiper d'acquisition future. |
| B7C-08 | Expirer et consommer | Les droits expirés ne sont plus disponibles ; une consommation annuelle prélève les tranches valides les plus anciennes en premier. |
| B7C-09 | Distinguer les types | Les congés maladie et exceptionnels suivent leur demande et leur validation sans diminuer le solde annuel. |
| B7C-10 | Notifier | Les acteurs concernés sont informés de la soumission, décision, demande de correction, retrait et changement de statut. |

## 4. Exigences d'accès et de sécurité

- Vérifier les droits côté serveur pour chaque lecture, téléchargement et action, pas seulement dans les menus.
- Le salarié ne consulte et ne modifie que ses propres demandes et soldes.
- Un manager ne traite que les demandes qui lui sont adressées ; la RH traite les demandes sans manager et dispose de la visibilité de gestion prévue.
- Un compte salarié est lié sans ambiguïté à `employe.id`. L'ajout du rôle Salarié ne doit pas supprimer les rôles Manager ou RH déjà associés.
- Les justificatifs, motifs et données de santé exigent une visibilité restreinte et une politique de conservation à définir.
- Les contrôles de solde et la réservation doivent être transactionnels afin d'éviter les doubles réservations.

## 5. Critères d'acceptation

1. Une tranche n'est créditée qu'à l'anniversaire d'un mois complet de service, à hauteur de 2,5 jours ouvrés.
2. Aucune acquisition postérieure à la date de soumission ne permet de faire passer une demande annuelle au contrôle de solde.
3. Une nouvelle période contractuelle ne reprend aucun solde du contrat précédent.
4. À consommation égale, les tranches les plus anciennes encore valides sont utilisées en premier ; chaque tranche cesse d'être disponible à son expiration.
5. Les demandes maladie et exceptionnelles ne diminuent pas le solde annuel.
6. Le salarié voit uniquement son dossier ; la demande va au manager direct, ou aux RH en l'absence de manager.
7. Refus, demande de correction, correction, retrait et notifications laissent une trace exploitable.

## 6. Limites et suivi

Le service et la commande `php spark conges:acquerir` calculent les tranches, expirations et allocations FIFO. La commande doit être planifiée quotidiennement. Les jours fériés doivent être configurés par la RH. L'envoi de notifications par email, la politique de justificatifs par type, la correction d'une demande déjà approuvée et le cas d'une demande dont les droits expirent pendant la validation restent hors du parcours actuel. L'unité jours ouvrés est une décision de projet dont la conformité juridique doit être vérifiée.
